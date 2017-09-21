<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParticipateInForumTest extends TestCase
{
	use RefreshDatabase;

    /**
     * @test
     */
    public function unauthenticated_users_may_not_add_replies()
    {
        $this->withExceptionHandling()
            ->post('threads/channel-slug/1/replies', [])
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function an_authenticated_user_may_participate_in_forum_threads()
    {
        //Give We have an authenticated user
        $this->be($user = factory('App\User')->create());
        
        // Have a existing thread
        $thread = factory('App\Thread')->create();

        //When the user adds a rely to the thread
        $reply = factory('App\Reply')->make();

        $this->post($thread->path() . '/replies', $reply->toArray());

        //Then their reply should be visible on the page.
        $this->get($thread->path())
            ->assertSee($reply->body);
    }

    /** @test */
    public function a_reply_requires_a_body()
    {
        $this->withExceptionHandling()->signedIn();
        $reply = make('App\Reply', ['body' => '']);

        $thread = create('App\Thread');

        return $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function unauthorized_user_can_not_delete_reply()
    {
        $this->withExceptionHandling();
        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('/login');

        $this->signedIn()            
            ->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function unauthorized_user_can_not_update_reply()
    {
        $this->withExceptionHandling();
        $reply = create('App\Reply');
        $change = [
            'body' => 'Body changed'
        ];
        $this->patch("/replies/{$reply->id}", $change)
            ->assertRedirect('/login');
    }

    /** @test */
    public function authorized_user_can_delete_reply()
    {
        $this->signedIn();
        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}");
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
    }

    /** @test */
    public function authorized_user_can_update_reply()
    {
        $this->signedIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);
        $change = [
            'body' => 'Body changed'
        ];

        $this->patch("/replies/{$reply->id}", $change);
        $this->assertDatabaseHas('replies', $change);
    }
}
