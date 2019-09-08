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
        $payload = $bot->getMessage()->getPayload();
        $slack = new SlackSDK(config('services.slack.oauth'));
        $userId = '';

        if (preg_match('/<@(.*?)>/', $name, $match) == 1) {
            // Log::info("User ID: ".$match[1]);
            $userId = $match[1];


            $user = $slack->users->info(array("user" => $userId));
            $user = $user['user'];

            $bot->say("Du wurdest von {$warner->getFirstname()} {$warner->getLastname()} abgemahnt!", $userId);

            $slack->chat->postMessage(array(
                "token" => config('services.slack.oauth'),
                "channel" => $payload->get('channel'),
                "text" => "*Das hast du dir verdient!* Abmahnung geht raus an {$name}!",
                "blocks" => json_encode([
                    [
                      "type" => "section",
                      "text" => [
                          "type" => "mrkdwn",
                          "text" => "*Das hast du dir verdient!* Abmahnung geht raus an {$name}!"
                      ]
                    ],
                    [
                      "type" => "image",
                      "title" => [
                          "type" => "plain_text",
                          "text" => "Abmahnung",
                          "emoji" => false
                      ],
                      "image_url" => "https://i.imgur.com/10lkUdy.gif",
                      "alt_text" => "Deine Reaktion"
                    ]
                  ])
            ));

        } else if ($name == "<!channel>") {

              $slack->chat->postMessage(array(
                  "token" => config('services.slack.oauth'),
                  "channel" => $payload->get('channel'),
                  "text" => "*Das habt ihr euch selbst eingebrockt!*",
                  "blocks" => json_encode([
                      [
                        "type" => "section",
                        "text" => [
                            "type" => "mrkdwn",
                            "text" => "*Das habt ihr euch verdient!* Ihr kriegt alle ne Abmahnung von {$warner->getFirstname()} {$warner->getLastname()}!"
                        ]
                      ],
                      [
                        "type" => "image",
                        "title" => [
                            "type" => "plain_text",
                            "text" => "Abmahnung",
                            "emoji" => false
                        ],
                        "image_url" => "https://i.imgur.com/10lkUdy.gif",
                        "alt_text" => "Eure Reaktion"
                      ]
                    ])
              ));

        } else {

            $bot->reply("Sprich halt bitte gescheit mit mir {$warner->getFirstname()}! Du kannst mich mit \"@channel abmahnen\" oder \"@mention abmahnen\" benutzen.");

        }

    }

}
