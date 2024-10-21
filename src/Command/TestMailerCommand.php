<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:test-mailer')]
class TestMailerCommand extends Command
{
private MailerInterface $mailer;

public function __construct(MailerInterface $mailer)
{
parent::__construct();
    $this->mailer = $mailer;
}

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output, ): int
{

$email = (new Email())
    ->from('contact@martin-julien-dev.fr')
    ->to('contact@martin-julien-dev.fr')
    ->subject('Mail blabla')
    ->html('<p>See Twig integration for better HTML integration!</p>');

    try {
        $this->mailer->send($email);
        $output->writeln('Email sent successfully');
        return Command::SUCCESS;
    } catch (\Exception $e) {
        $output->writeln('Failed to send email: ' . $e->getMessage());
        return Command::FAILURE;
    }
}
}
