<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\CommentGrep;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class CommentGrepCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('hydra:comment:grep')
            ->setDescription('comment grep - comments pattern searcher')
            ->addArgument('pattern', InputArgument::REQUIRED)
            ->addArgument('authors', InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pattern = $input->getArgument('pattern');
        $authors = $input->getArgument('authors');

        $this->createReport($pattern, $authors);
    }

    /**
     * @param string $pattern
     * @param array $author
     */
    protected function createReport($pattern, array $author)
    {
        /** @var CommentGrep $report */
        $report = $this->getContainer()->get('hydra_big_hydra.jira.commentgrep');
        $filename = $report->runGrep($pattern, $author);
        echo $filename;
        echo "\n";
    }
}
