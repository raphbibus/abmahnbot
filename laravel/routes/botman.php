<?php
use App\Http\Controllers\BotManController;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');

$botman->hears('{name} abmahnen', function ($bot, $name) {

  $warner = $bot->getUser();

  Log::info($name);
  $userId = '';
  if (preg_match('/<@(.*?)>/', $name, $match) == 1) {
    Log::info($match[1]);
    $userId = $match[1];

    $slack = new wrapi\slack\slack(config('services.slack.oauth'));

    $user = $slack->users->info(array("user" => $userId));
    $user = $user['user'];

    $bot->reply($user['real_name'].' kriegt ne Abmahnung!');
    $bot->say("Du wurdest von {$warner->getFirstname()} {$warner->getLastname()} abgemahnt!", $userId);

  } else if ($name == "<!channel>") {

    $bot->reply("Ihr kriegt alle ne Abmahnung von {$warner->getFirstname()} {$warner->getLastname()}!");

  } else {

    $bot->reply("Sprich halt bitte gescheit mit mir {$warner->getFirstname()}! Du kannst mich mit \"@channel abmahnen\" oder \"@mention abmahnen\" benutzen.");

  }

});
