<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Thread;
use App\Reply;

class RepliesController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

    public function store(Thread $thread)
    {
		$data = request()->validate([
			'body' => 'required',			
		]);

		$data['user_id'] = auth()->id();

		$thread->addReply($data);

		return back();
    }
}