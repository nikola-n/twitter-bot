<?php

use Codebird\Codebird;
use MonkeyLearn\Client as MonkeyLearn;

require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=twitter-bot', 'root', 'root');

$ml = new MonkeyLearn('a71aa30c686c6087637cb288b13e8b06a018ffd0');

Codebird::setConsumerKey('PbXmtfcKHEckEPnvzlvGYrMQF', 'HrBsf5zvCKwYiPxnQXNqm8lVKUxUycG1Qy6zPLYTnXWTJWDdqA');

$cb = Codebird::getInstance();
$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);

$cb->setToken('4778628941-egXbYAeaed6JciTw6Xd4bQx3stSRU2euDXo5Fsi', 'w4mzvZi7AlevQ588oELOIuDPwJ1UobLK3KJxo2170Csc7');

$lastId = $db->query("SELECT * FROM tracking ORDER BY twitter_id DESC LIMIT 1")
    ->fetch(PDO::FETCH_OBJ);

$mentions = $cb->statuses_mentionsTimeline($lastId ? 'since_id=' . $lastId->twitter_id : '');

if ( ! isset($mentions[0])) {
    return;
}

$happyEmojis = [
    '&#x1F601',
    '&#x1F602',
];

$neutralEmojis = [
    '&#x1F610',
    '&#x1F611',
];

$sadEmojis = [
    '&#x1F6120',
    '&#x1F6121',
];

$tweets = [];
foreach ($mentions as $index => $mention) {
    if (isset($mention['id'])) {
        $tweets[] = [
            'id'               => $mention['id'],
            'user_screen_name' => $mention['user']['screen_name'],
            'text'             => $mention['text'],
        ];
    }
}

$tweetsText = array_map(function ($tweet) {
    return $tweet['text'];
}, $tweets);

$analysis = $ml->classifiers->classify('cl_pi3C7JiL', $tweetsText, true);


var_dump($analysis);
foreach ($tweets as $index => $tweet) {
    switch (strtolower($analysis->result[$index][0]['label'])) {
        case 'positive':
            $emojiSet = $happyEmojis;
            break;
        case 'neutral':
            $emojiSet = $neutralEmojis;
            break;
        case'negative':
            $emojiSet = $sadEmojis;
            break;
    }
    $cb->statuses_update([
        'status'                => '@' . $tweet['user_screen_name'] . ' ' . html_entity_decode($emojiSet[rand(0, count($emojiSet) - 1)], 0, 'UTF-8'),
        'in_reply_to_status_id' => $tweet['id'],
    ]);

    $track = $db->prepare("INSERT INTO tracking (twitter_id) VALUES (:twitterId)");
    $track->execute([
        'twitterId' => $tweet['id'],
    ]);
}