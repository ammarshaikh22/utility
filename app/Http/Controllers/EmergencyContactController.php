<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\StoreEmergencyContactRequest;
use App\Models\EmergencyContact;

class EmergencyContactController extends AccountBaseController
{
    /**
     * Show the form to create a new emergency contact.
     *
     * @return string Rendered HTML view for creating an emergency contact
     */
    public function create()
    {
        $this->pageTitle = __('app.addContact');
        $this->userId = request()->user_id ? request()->user_id : null;

        return view('profile-settings.emergency-contacts.create', $this->data)->render();
    }

    /**
     * Store a newly created emergency contact in storage.
     *
     * @param  StoreEmergencyContactRequest  $request
     * @return array JSON response with success message and updated HTML
     */
    public function store(StoreEmergencyContactRequest $request)
    {
        $emergencyContact = new EmergencyContact();
        $emergencyContact->user_id = $request->user_id ?? user()->id;
        $emergencyContact->name = $request->name;
        $emergencyContact->mobile = $request->mobile;
        $emergencyContact->email = $request->email;
        $emergencyContact->relation = $request->relationship;
        $emergencyContact->address = $request->address;
        $emergencyContact->added_by = user()->id;
        $emergencyContact->save();

        $this->contacts = EmergencyContact::where('user_id', $emergencyContact->user_id)->get();
        $html = view('profile-settings.emergency-contacts.data', $this->data)->render();

        return Reply::successWithData(__('messages.employeeEmergencyContact'), ['html' => $html]);
    }

    /**
     * Display a specific emergency contact.
     *
     * @param  EmergencyContact  $emergencyContact
     * @return string Rendered HTML view of the contact
     */
    public function show(EmergencyContact $emergencyContact)
    {
        $this->managePermission = user()->permission('manage_emergency_contact');

        abort_403(
            !($this->managePermission == 'all'
            || ($emergencyContact->added_by == user()->id)
            || ($emergencyContact->user_id == user()->id))
        );

        $this->pageTitle = __('modules.emergencyContact.emergencyContact');
        $this->contact = $emergencyContact;

        return view('profile-settings.emergency-contacts.show', $this->data)->render();
    }

    /**
     * Show the form for editing an existing emergency contact.
     *
     * @param  EmergencyContact  $emergencyContact
     * @return string Rendered HTML view for editing the contact
     */
    public function edit(EmergencyContact $emergencyContact)
    {
        $this->managePermission = user()->permission('manage_emergency_contact');

        abort_403(
            !($this->managePermission == 'all'
            || ($emergencyContact->added_by == user()->id)
            || ($emergencyContact->user_id == user()->id))
        );

        $this->pageTitle = __('app.editContact');
        $this->contact = $emergencyContact;

        return view('profile-settings.emergency-contacts.edit', $this->data)->render();
    }

    /**
     * Update the specified emergency contact in storage.
     *
     * @param  StoreEmergencyContactRequest  $request
     * @param  EmergencyContact  $emergencyContact
     * @return array JSON response with success message and updated HTML
     */
    public function update(StoreEmergencyContactRequest $request, EmergencyContact $emergencyContact)
    {
        $this->managePermission = user()->permission('manage_emergency_contact');

        abort_403(
            !($this->managePermission == 'all'
            || ($emergencyContact->added_by == user()->id)
            || ($emergencyContact->user_id == user()->id))
        );

        $emergencyContact->name = $request->name;
        $emergencyContact->mobile = $request->mobile;
        $emergencyContact->email = $request->email;
        $emergencyContact->relation = $request->relationship;
        $emergencyContact->address = $request->address;
        $emergencyContact->last_updated_by = user()->id;
        $emergencyContact->save();

        $this->contacts = EmergencyContact::where('user_id', $emergencyContact->user_id)->get();
        $html = view('profile-settings.emergency-contacts.data', $this->data)->render();

        return Reply::successWithData(__('messages.employeeEmergencyContact'), ['html' => $html]);
    }

    /**
     * Remove the specified emergency contact from storage.
     *
     * @param  EmergencyContact  $emergencyContact
     * @return array JSON response with success message and redirect URL
     */
    public function destroy(EmergencyContact $emergencyContact)
    {
        $this->managePermission = user()->permission('manage_emergency_contact');

        abort_403(
            !($this->managePermission == 'all'
            || ($emergencyContact->added_by == user()->id)
            || ($emergencyContact->user_id == user()->id))
        );

        $emergencyContact->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), [
            'redirectUrl' => route('profile-settings.index') . '?tab=emergency-contacts'
        ]);
    }
}
