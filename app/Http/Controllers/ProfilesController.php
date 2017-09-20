<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Activity;

class ProfilesController extends Controller
{
    public function show(User $user)
    {
    	return view('profiles.show', [
    		'user' => $user,
    		'activities' => Activity::feed($user)
    	]);
    }

    protected function getActivities($user)
    {
    	return $user->activity()
    		->latest()->with('subject')
    		->get()
    		->groupBy(function ($activity) {
    			return $activity->created_at->format('Y-m-d');
    		});
    }

}