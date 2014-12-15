<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\MentionedReport;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class MentionedInCommentCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('hydra:report:mentioned')
            ->setDescription('Team member mentioned in comment')
            ->addArgument('mentioned', InputArgument::REQUIRED)
            ->addArgument('authors', InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mentioned = $input->getArgument('mentioned');
        $authors = $input->getArgument('authors');

        $this->createReport($mentioned, $authors);
    }

    /**
     * @param string $mentioned
     * @param array $author
     */
    protected function createReport($mentioned, array $author)
    {
        /** @var MentionedReport $report */
        $report = $this->getContainer()->get('hydra_big_hydra.jira.mentionedreport');
        $filename = $report->getMentionedInComment($mentioned, $author);
        echo $filename;
        echo "\n";
    }
}
