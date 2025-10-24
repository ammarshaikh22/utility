<?php

namespace App\Http\Controllers;

use App\Events\NewProjectEvent;
use App\Helper\Reply;
use App\Http\Requests\Project\StoreRating;
use App\Models\ProjectRating;
use Illuminate\Http\Request;

class ProjectRatingController extends AccountBaseController
{

    /**
     * Store a new project rating in the database.
     * Verifies add permission, saves the rating details, triggers a project event, and notifies project members.
     *
     * @param  \App\Http\Requests\Project\StoreRating  $request
     * @return array
     */
    public function store(StoreRating $request)
    {
        $addProjectRatingPermission = user()->permission('add_project_rating');
        abort_403(!in_array($addProjectRatingPermission, ['all', 'added', 'owned', 'both']));

        $rating = new ProjectRating();
        $rating->rating = $request->rating;
        $rating->comment = $request->comment;
        $rating->user_id = $this->user->id;
        $rating->project_id = $request->project_id;
        $rating->added_by = user()->id;
        $rating->last_updated_by = user()->id;
        $rating->save();

        $members = $rating->project->projectMembers;

        event(new NewProjectEvent($rating->project, $members, 'ProjectRating'));

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Update an existing project rating in the database.
     * Verifies edit permission and updates the rating details, including the user who made the update.
     *
     * @param  \App\Http\Requests\Project\StoreRating  $request
     * @param  int  $id
     * @return array
     */
    public function update(StoreRating $request, $id)
    {
        $addProjectRatingPermission = user()->permission('edit_project_rating');
        abort_403(!in_array($addProjectRatingPermission, ['all', 'added', 'owned', 'both']));

        $rating = ProjectRating::findOrFail($id);
        $rating->rating = $request->rating;
        $rating->comment = $request->comment;
        $rating->user_id = $this->user->id;
        $rating->project_id = $request->project_id;
        $rating->added_by = user()->id;
        $rating->last_updated_by = user()->id;
        $rating->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Delete a specific project rating from the database.
     * Verifies delete permission based on user roles and project association, then removes the rating.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        $deleteRatingPermission = user()->permission('delete_project_rating');
        $rating = ProjectRating::findOrFail($id);
        $memberIds = $rating->project->members->pluck('user_id')->toArray();

        abort_403(
            !(is_null($rating->project->deleted_at) &&
            $deleteRatingPermission == 'all'
            || ($deleteRatingPermission == 'added' && $rating->project->rating->added_by == user()->id)
            || ($deleteRatingPermission == 'owned' && (in_array(user()->id, $memberIds) || $rating->project->client_id == user()->id))
            || ($deleteRatingPermission == 'both' && (in_array(user()->id, $memberIds) || $rating->project->client_id == user()->id || $rating->project->rating->added_by == user()->id))
            || in_array('client', user_roles()))
        );

        ProjectRating::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

}