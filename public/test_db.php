<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$posts = $em->getRepository('App\Entity\Post')->findAll();
echo 'Success! Found ' . count($posts) . ' posts.';
