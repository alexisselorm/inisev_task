<?php

namespace App\Console\Commands;

use App\Mail\SendMail;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mails to subscribers of a website ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Use database transactions to ensure data consistency
        DB::beginTransaction();

        try {
            // Get all posts that are not published in 'chunks' for performance purposes.

            Post::query()->where('published', 0)->chunk(1000, function ($all_posts) {

                $all_posts->each(function ($post) {

                    $website = Website::query()->where('id', $post->website_id)->first();

                    if (!empty($website)) {

                        // Get subscriptions of selected website

                        Subscription::where('website_id', $website->id)->chunk(1000, function ($subscriptions) use ($post) {
                            $subscriptions->each(function ($subscription) use ($post) {

                                $email = [
                                    'name' => $subscription->user->name,
                                    'email' => $subscription->user->email,
                                    'subject' => 'Email Notification: ' . $post->title,
                                    'title' => $post->title,
                                    'body' => $post->description,
                                ];

                                Mail::to($email['email'])->send(new SendMail($email));

                                Log::alert('>>>>>>>>>>>> POST WAS PUBLISHED SUCCESSFULLY <<<<<<<<<<');

                            });

                        });

                    }

                    DB::table('posts')->where('id', $post->id)->update(['published' => true]);

                    DB::commit();

                });

            });

        } catch (\Exception$exception) {
            // Incase of a failure, rollback changes and log the error
            DB::rollBack();

            Log::alert('>>>>>>>>>>>> PUBLISHING ERROR <<<<<<<<<<<<< MESSAGE: ' . $exception->getMessage() . '>>>>>>>>>>>> LINE: <<<<<<<<<<<<<' . $exception->getLine());

        }

    }
}
