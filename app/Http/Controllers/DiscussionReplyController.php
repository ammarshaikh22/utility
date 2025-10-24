<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\DiscussionReply\StoreRequest;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Project;
use App\Helper\UserService;

/**
 * Class DiscussionReplyController
 *
 * Manages replies within project discussions.
 * Includes creating, storing, retrieving, editing,
 * updating, and deleting replies.
 */
class DiscussionReplyController extends AccountBaseController
{
    /**
     * Show the form to create a new reply for a discussion.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->discussionId = request('id');
        return view('discussions.replies.create', $this->data);
    }

    /**
     * Store a new reply for a discussion.
     *
     * - Saves the reply content.
     * - Refreshes project member data.
     * - Returns updated discussion replies view.
     *
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $this->userId = UserService::getUserId();

        $reply = new DiscussionReply();
        $reply->user_id = $this->userId;
        $reply->discussion_id = $request->discussion_id;
        $reply->body = trim_editor($request->description);
        $reply->added_by = user()->id;
        $reply->save();

        // Prepare project members data
        $project = Project::findOrFail($reply->discussion->project_id);
        $userData = [];
        foreach ($project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);

        $html = view('discussions.replies.show', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'discussion_reply_id' => $reply->id
        ]);
    }

    /**
     * Get all replies for a specific discussion.
     *
     * @param int $id
     * @return array
     */
    public function getReplies($id)
    {
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($id);
        $this->userId = UserService::getUserId();

        // Prepare project members data
        $project = Project::findOrFail($this->discussion->project_id);
        $userData = [];
        foreach ($project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $html = view('discussions.replies.show', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html
        ]);
    }

    /**
     * Show the edit form for a specific reply.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->reply = DiscussionReply::findOrFail($id); /* @phpstan-ignore-line */
        return view('discussions.replies.edit', $this->data);
    }

    /**
     * Update a specific reply.
     *
     * - Updates reply body and metadata.
     * - Refreshes discussion replies and project members data.
     *
     * @param StoreRequest $request
     * @param int $id
     * @return array
     */
    public function update(StoreRequest $request, $id)
    {
        $reply = DiscussionReply::findOrFail($id);
        $reply->body = trim_editor($request->description);
        $reply->added_by = user()->id;
        $reply->save();

        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);
        $this->userId = UserService::getUserId();

        // Prepare project members data
        $userData = [];
        foreach ($this->discussion->project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $html = view('discussions.replies.show', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html
        ]);
    }

    /**
     * Delete a specific reply.
     *
     * - Removes reply from database.
     * - Refreshes discussion replies and project members data.
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $reply = DiscussionReply::findOrFail($id);
        $reply->delete();

        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);
        $this->userId = UserService::getUserId();

        // Prepare project members data
        $project = Project::findOrFail($this->discussion->project_id);
        $userData = [];
        foreach ($project->projectMembers as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);

        $html = view('discussions.replies.show', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html
        ]);
    }
}
