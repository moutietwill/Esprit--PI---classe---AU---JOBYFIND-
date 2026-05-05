<?php
require "app/config/Database.php";
require "app/repositories/EventRepository.php";
$repo = new EventRepository();
$event = new Event(["titre"=>"TEST".time(),"description"=>"desc","date"=>"2026-12-31","lieu"=>"test","idOrganisateur"=>1]);
$result = $repo->create($event);
echo $result ? "OK " . $event->getId() : "FAIL";
?>
