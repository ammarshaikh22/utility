<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Discussion\StoreRequest;
use App\Models\Discussion;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Helper\UserService;

/**
 * Class DiscussionController
 *
 * Handles project discussion-related operations such as
 * creating, storing, showing, deleting discussions,
 * and marking best answers.
 */
class DiscussionController extends AccountBaseController
{
    /**
     * DiscussionController constructor.
     *
     * Sets the page title for all discussion views.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.projects.discussion');
    }

    /**
     * Show the create discussion form.
     *
     * - Verifies user permission to add discussions.
     * - Prepares project members' data for assigning.
     * - Fetches discussion categories.
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_project_discussions');
        $this->projectId = request('id');
        $project = Project::findOrFail($this->projectId);
        $userId = UserService::getUserId();

        // Prepare project members data for front-end usage
        $userData = [];
        foreach ($project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        // Check permission (all, added, or project admin)
        abort_403(!(in_array($this->addPermission, ['all', 'added']) || $project->project_admin == $userId));

        $this->categories = DiscussionCategory::orderBy('order', 'asc')->get();
        $this->redirectUrl = request('redirectUrl');

        return view('discussions.create', $this->data);
    }

    /**
     * Store a new discussion and its initial reply.
     *
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $discussion = new Discussion();
        $discussion->title = $request->title;
        $discussion->discussion_category_id = $request->discussion_category;
        $userId = UserService::getUserId();

        if ($request->has('project_id')) {
            $discussion->project_id = $request->project_id;
        }

        $discussion->last_reply_at = now()->timezone('UTC')->toDateTimeString();
        $discussion->user_id = $userId;
        $discussion->save();

        // Create initial discussion reply
        $discussionReply = DiscussionReply::create([
            'body' => $request->description,
            'user_id' => $userId,
            'discussion_id' => $discussion->id,
            'added_by' => user()->id
        ]);

        $redirectUrl = urldecode($request->redirect_url) ?: route('projects.index');

        return Reply::successWithData(__('messages.recordSaved'), [
            'discussion_id' => $discussion->id,
            'discussion_reply_id' => $discussionReply->id,
            'redirectUrl' => $redirectUrl
        ]);
    }

    /**
     * Display a discussion with replies.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($id);
        $viewPermission = user()->permission('view_project_discussions');
        $this->userId = UserService::getUserId();

        // Check permission to view discussion
        abort_403(!($viewPermission == 'all' || ($viewPermission == 'added' && $this->discussion->added_by == $this->userId)));

        $project = Project::findOrFail($this->discussion->project_id);

        // Prepare project members data
        $userData = [];
        foreach ($project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $this->view = 'discussions.replies.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return redirect(route('projects.show', $this->discussion->project_id) . '?tab=discussion');
    }

    /**
     * Delete a discussion if the user has permission.
     *
     * @param int $id
     * @return array
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($id);
        $deletePermission = user()->permission('delete_project_discussions');
        $userId = UserService::getUserId();

        // Permission check (all or added)
        abort_403(!($deletePermission == 'all' || ($deletePermission == 'added' && $this->discussion->added_by == $userId)));

        Discussion::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Set or unset a reply as the best answer for a discussion.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function setBestAnswer(Request $request)
    {
        $this->userId = UserService::getUserId();
        $reply = DiscussionReply::findOrFail($request->replyId);
        $editPermission = user()->permission('edit_project_discussions');

        // Permission check (all or added)
        abort_403(!($editPermission == 'all' || ($editPermission == 'added' && $reply->discussion->added_by == $this->userId)));

        $replyId = ($request->type == 'set') ? $request->replyId : null;

        // Update best answer
        Discussion::where('id', $reply->discussion_id)->update(['best_answer_id' => $replyId]);

        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);

        // Prepare project members data
        $userData = [];
        foreach ($reply->discussion->project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();

        return $this->returnAjax('discussions.replies.show');
    }
}
