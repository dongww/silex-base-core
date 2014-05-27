<?php
/**
 * User: dongww
 * Date: 14-5-26
 * Time: 上午9:39
 */

namespace Dongww\Db\Dbal\Command;

use Doctrine\DBAL\Connection;
use Dongww\Db\Dbal\Checker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    protected $conn;

    function __construct(Connection $conn, $name = null)
    {
        $this->conn = $conn;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('db:update')
            ->setDescription('更新数据库。');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start checking the database!');

        $checker = new Checker($this->conn);

        $sql = $checker->getDiffSql('app/config/db/structure.yml');

        if (empty($sql)) {
            $output->writeln('No changed!');
        } else {
            $output->writeln('Start updating the database!');
            $output->writeln('//sql--------------------');
            foreach ($sql as $s) {
                $output->writeln($s . ';');
                $this->conn->query($s);
            }
            $output->writeln('\\\\end sql----------------');
            $output->writeln('Database updated!');
        }
    }
}
