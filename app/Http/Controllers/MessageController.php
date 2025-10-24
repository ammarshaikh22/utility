<?php

namespace App\Http\Controllers;

use Client;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Project;
use App\Models\UserChat;
use App\Models\ProjectMember;
use App\Http\Requests\ChatStoreRequest;
use Illuminate\Support\Facades\Session;
use App\Helper\Common;

class MessageController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.messages';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('messages', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display the messaging interface with a list of recent user conversations.
     * Handles AJAX search requests for users or messages and filters users based on roles and permissions.
     * For clients, restricts employee visibility based on message settings and project assignments.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        session()->forget('message_setting');
        session()->forget('pusher_settings');
        $this->messageSetting = message_setting();

        abort_403($this->messageSetting->allow_client_admin == 'no' && $this->messageSetting->allow_client_employee == 'no' && in_array('client', user_roles()));

        if (request()->ajax() && request()->has('term')) {
            $term = (request('term') != '') ? request('term') : null;

            if ($term === null) {

                $userLists = UserChat::userListLatest(user()->id, null);
                $messageIds = collect($userLists)->pluck('id');
            } else {

                // Prevent blind SQL injection by using parameter binding and not interpolating user input directly
                $safeTerm = Common::safeString(request()->get('term'));

                $userLists = UserChat::userListLatest(user()->id, $safeTerm);

                $messages = UserChat::where(function ($query) use ($safeTerm) {
                    $query->where('message', 'LIKE', '%' . $safeTerm . '%');
                })
                    ->where(function ($query) {
                        $query->where('from', user()->id)
                            ->orWhere('to', user()->id);
                    })
                    ->get();
                $messageIds = collect($userLists)->pluck('id')->merge($messages->pluck('id'))->unique();
            }

            $this->userLists = UserChat::with(['fromUser' => function ($q) {
                $q->withCount(['unreadMessages']);
            }, 'toUser' => function ($q) {
                $q->withCount(['unreadMessages']);
            }])
                ->whereIn('id', $messageIds)
                ->orderByDesc('id')->get();

            $userList = view('messages.user_list', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'userList' => $userList]);
        }

        if (request()->clientId) {
            $this->client = User::findOrFail(request()->clientId);
        }

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');

        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }])
            ->whereIn('id', $messageIds)->orderByDesc('id')->get();

        if (in_array('client', user_roles())) {
            if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::allEmployees(null, true);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->project_id = Project::where('client_id', user()->id)->pluck('id');
                $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');
                $this->employees = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->employees = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->employees = [];
            }
        } else {
            $this->employees = User::allEmployees(null, true, 'all');
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);

        return view('messages.index', $this->data);
    }

    /**
     * Display the interface for starting a new conversation.
     * Prepares a list of users (employees or clients) based on user roles and message settings.
     * Restricts client visibility for employees based on project assignments if configured.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->messageSetting = message_setting();
        $this->project_id = Project::where('client_id', user()->id)->pluck('id');
        $this->employee_project_id = ProjectMember::where('user_id', user()->id)->pluck('project_id');
        $this->employee_client_id = Project::whereIn('id', $this->employee_project_id)->pluck('client_id');

        $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');

        if (!in_array('client', user_roles())) {
            $this->employees = User::allEmployees($this->user->id, true, 'all');

            if (in_array('admin', user_roles())) {
                $this->clients = User::allClients();
            } elseif (($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no')) {
                $this->clients = User::allClients();
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->clients = User::whereIn('id', $this->employee_client_id)->get();
            }
        }

        // This will return true if message button from projects overview button is clicked
        if (request()->clientId) {
            $this->clientId = request()->clientId;
            $this->client = User::findOrFail(request()->clientId);
        }

        if (in_array('client', user_roles())) {
            if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::allEmployees(null, true);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->employees = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->employees = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->employees = [];
            }
        } else {
            $this->employees = User::allEmployees(null, true, 'all');
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        return view('messages.create', $this->data);
    }

    /**
     * Store a new chat message in the database.
     * Validates the message content and saves it to the UserChat model.
     * Returns updated user and message lists for real-time UI updates.
     *
     * @param  \App\Http\Requests\ChatStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ChatStoreRequest $request)
    {
        if ($request->user_type == 'client') {
            $receiverID = $request->client_id;
        } else {
            $receiverID = $request->user_id;
        }

        $message = $request->message;

        if ($request->types == 'chat') {
            $validateModule = $this->validateModule($message);

            if ($validateModule['status'] == false) {
                return Reply::error($validateModule['message']);
            }
        }

        $message = new UserChat();
        $message->message         = $request->message;
        $message->user_one        = user()->id;
        $message->user_id         = $receiverID;
        $message->from            = user()->id;
        $message->to              = $receiverID;
        $message->notification_sent = 0;
        $message->save();

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $this->userLists = UserChat::with('fromUser', 'toUser')->whereIn('id', $messageIds)->orderByDesc('id')->get();
        $userList = view('messages.user_list', $this->data)->render();

        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('messages.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $receiverID, 'userName' => $message->toUser->name]);
    }

    /**
     * Display the conversation details for a specific user.
     * Marks messages as read and returns the updated message list for the UI.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->chatDetails = UserChat::chatDetail($id, user()->id);

        // Mark messages read
        $updateData = ['message_seen' => 'yes'];
        UserChat::messageSeenUpdate($this->user->id, $id, $updateData);
        $this->unreadMessage = (request()->unreadMessageCount > 0) ? 0 : 1;
        $this->userId = $id;

        $view = view('messages.message_list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view, 'unreadMessages' => $this->unreadMessage, 'id' => $this->userId]);
    }

    /**
     * Delete a single chat message by its ID.
     * Returns the updated chat details to reset the UI if necessary.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userChats = UserChat::findOrFail($id);

        // Delete chat
        UserChat::destroy($id);

        // To reset chat-box if deleted chat is last one between them
        $chatDetails = UserChat::chatDetail($userChats->from, $userChats->to);

        return Reply::successWithData(__('messages.deleteSuccess'), ['chat_details' => $chatDetails]);
    }

    /**
     * Delete all messages between the authenticated user and another user.
     * Removes all chat records in both directions (from and to the specified user).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyAll($id)
    {
        UserChat::where(function ($query) use ($id) {
            $query->where('from', user()->id)
                ->where('to', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('from', $id)
                ->where('to', user()->id);
        })->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Fetch and render the updated list of recent user conversations.
     * Used to refresh the user list in the UI, optionally highlighting a specific user.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchUserListView()
    {
        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }])
            ->whereIn('id', $messageIds)->orderByDesc('id')->get();

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);
        $userList = view('messages.user_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList]);
    }

    /**
     * Fetch and render the conversation messages for a specific user.
     * Used to update the message list in the UI for a selected user.
     *
     * @param  int  $receiverID
     * @return \Illuminate\Http\Response
     */
    public function fetchUserMessages($receiverID)
    {
        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('messages.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }

    /**
     * Check for new unread messages for the authenticated user.
     * Marks notifications as sent and returns the count of new messages.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkNewMessages()
    {
        $newMessageCount = UserChat::where('to', user()->id)->where('message_seen', 'no')->where('notification_sent', 0)->count();

        UserChat::where('to', user()->id)->update(['notification_sent' => 1]); // Mark notification as sent

        return Reply::dataOnly(['new_message_count' => $newMessageCount]);
    }

    /**
     * Validate if a chat message is non-empty.
     * Returns an error if the message is empty, otherwise confirms validity.
     *
     * @param  string  $message
     * @return array
     */
    public function validateModule($message)
    {
        if ($message == '') {

            return [
                'status' => false,
                'message' => __('messages.fileMessage'),
            ];
        } else {
            return [
                'status' => true,
            ];
        }
    }
}