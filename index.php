<?php

use Codebird\Codebird;
use MonkeyLearn\Client as MonkeyLearn;

require 'vendor/autoload.php';

$ml = new MonkeyLearn('a71aa30c686c6087637cb288b13e8b06a018ffd0');

Codebird::setConsumerKey('PbXmtfcKHEckEPnvzlvGYrMQF', 'HrBsf5zvCKwYiPxnQXNqm8lVKUxUycG1Qy6zPLYTnXWTJWDdqA');

$cb = Codebird::getInstance();
$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);

$cb->setToken('4778628941-egXbYAeaed6JciTw6Xd4bQx3stSRU2euDXo5Fsi', 'w4mzvZi7AlevQ588oELOIuDPwJ1UobLK3KJxo2170Csc7');

$mentions = $cb->statuses_mentionsTimeline();

if ( ! isset($mentions[0])) {
    return;
}

$happyEmojis = [
  '&#x1F601',
  '&#x1F602',
];

$neutralEmojis = [
    '&#x1F610',
    '&#x1F611'
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

$analysis   = $ml->classifiers->classify('cl_pi3C7JiL', $tweetsText, true);

foreach($tweets as $index => $tweet)
{
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
   var_dump($emojiSet);
}