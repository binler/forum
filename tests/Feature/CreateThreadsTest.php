<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateThreadsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function an_authenticated_user_can_create_new_forum_threads()
    {
        //Give we have a signed in user
        $this->signedIn();

        //When we hit the Endpoint to create a new thread
        $thread = make('App\Thread');
        
        $response = $this->post('/threads', $thread->toArray());

        //Then, When we visit the thread page, We should see the new thread
        $this->get($response->headers->get('Location'))
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /**
     * @test
     */
    public function guest_may_not_create_threads()
    {
        $thread = make('App\Thread');
        $this->withExceptionHandling()
            ->post('/threads', $thread->toArray())
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */

    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');

    }

    /** @test */

    public function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');

    }

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {
        factory('App\Channel', 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');
    }

    public function publishThread($overrides = [])
    {
        $this->withExceptionHandling()->signedIn();
        $thread = make('App\Thread', $overrides);

        return $this->post('/threads', $thread->toArray());
    }
}