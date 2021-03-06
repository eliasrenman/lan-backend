<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EventRegistrations;
use App\Event;
use App\User;
use Vinkla\Hashids\Facades\Hashids;
use App\Events\RegistrationUpdated;



class EventRegistrationsController extends Controller
{
    public function index($event) {
        return User::rightJoin('registrations', 'registrations.user_id', '=', 'users.id')->where('registrations.event_id', '=', $event)->get();
    }

    public function store(Request $request) {
        $validatedRegistration = $request->validate([
            'event_id' => 'required',
            'group_code' => 'nullable|string|max:24',
            'guardian' => 'nullable|max:32',
            'setup_type' => 'required',
        ]);

        $event = Event::findOrFail($request->event_id);
        if(date(now()) < $event->registration_closes_at){
            $data = collect(EventRegistrations::create([
                'user_id' => $request->user()->id,
                'event_id' => $validatedRegistration['event_id'],
                'guardian' => $validatedRegistration['guardian'],
                'group_code' => strtolower($validatedRegistration['group_code']),
                'setup_type' => $validatedRegistration['setup_type']
            ]));
            
            $hashedId = Hashids::encode($data['id']);
            $data->put('hashid', $hashedId);
            return [
                'message' => 'Registration successful',
                'data' => $data
            ];
        }
    }

    public function update($hashid) {
        $id = Hashids::decode($hashid);

        EventRegistrations::where('id', $id)->update(['checked_in'=> 1]);
        $data = EventRegistrations::where('id', $id)->first();
        RegistrationUpdated::dispatch($data);
        return [
            'message' => 'Registration successful',
            'data' => $data
        ];
    }
    
    public function patch(Request $request, EventRegistrations $registration) {
        $validatedRegistration = $request->validate([
            'checked_in' => 'nullable|integer',
            'event_id' => 'nullable|integer',
            'group_code' => 'nullable|alpha_dash',
            'guardian' => 'nullable|max:32',
            'setup_type' => 'nullable',
            'room_id' => 'nullable|integer'
        ]);
        $registration->update($validatedRegistration);
        RegistrationUpdated::dispatch($registration);

        return [
            'message' => 'Successful update',
            'data' => $registration
        ];
    }
    
    public function updateRoom(Request $request){
        $code = $request->group_code ? $request->group_code : '';

        $data = EventRegistrations::where('group_code', $code)
            ->update(['room_id' => $request->room_id]);
        return [
            'message' => 'Success',
            'data' => $data
        ];
    }

    public function show(Request $request, $event) {
        $registration = EventRegistrations::where('user_id', $request->user()->id)
        ->where('event_id', $event)
        ->firstOrFail();

        $collection = collect($registration);
        
        
        $collection->put('hashid', Hashids::encode($collection['id']));
        $collection->put('room', $registration->room);
        return $collection;
    }

}