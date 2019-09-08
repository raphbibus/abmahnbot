<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;
use Log;
use wrapi\slack\slack as SlackSDK;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {
        $bot->startConversation(new ExampleConversation());
    }

    public function abmahnen(BotMan $bot, $name) {

        $warner = $bot->getUser();

        // Log::info($name);
        $userId = '';
        if (preg_match('/<@(.*?)>/', $name, $match) == 1) {
            // Log::info($match[1]);
            $userId = $match[1];

            $slack = new SlackSDK(config('services.slack.oauth'));

            $user = $slack->users->info(array("user" => $userId));
            $user = $user['user'];

            $bot->reply($user['real_name'].' kriegt ne Abmahnung!');
            $bot->say("Du wurdest von {$warner->getFirstname()} {$warner->getLastname()} abgemahnt!", $userId);

        } else if ($name == "<!channel>") {

            $bot->reply("Ihr kriegt alle ne Abmahnung von {$warner->getFirstname()} {$warner->getLastname()}!");

        } else {

            $bot->reply("Sprich halt bitte gescheit mit mir {$warner->getFirstname()}! Du kannst mich mit \"@channel abmahnen\" oder \"@mention abmahnen\" benutzen.");

        }

    }

}
