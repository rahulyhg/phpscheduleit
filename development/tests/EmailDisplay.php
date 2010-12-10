<?php
define('ROOT_DIR', dirname(__FILE__) . '/../');
require_once(ROOT_DIR . "lib/Email/namespace.php");
require_once(ROOT_DIR . "lib/Email/Messages/ReservationCreatedEmail.php");
require_once(ROOT_DIR . "lib/Domain/namespace.php");
require_once(ROOT_DIR . "tests/fakes/namespace.php");


$start = Date::Parse('2010-10-05 03:30:00', 'UTC');
$end = Date::Parse('2010-10-06 13:30:00', 'UTC');

$reservation = new Reservation();
$reservation->Update(1, 1, 'crazy title', 'super description');
$reservation->UpdateDuration(new DateRange($start, $end));

$reservation->Repeats(new DayOfMonthRepeat(1, $end->AddDays(100), new DateRange($start, $end)));

$user = new FakeUser();
$user->SetLanguage('en_gb');

$email = new ReservationCreatedEmail($user, $reservation, new FakeResource(1, 'name'));
echo $email->Body();

$emailService = new EmailService();
$emailService->Send($email);

?>