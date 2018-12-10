<?php

    namespace caspar\core\cli;

    use caspar\core\CliCommand;

    /**
     * CLI command class, main -> create_tables
     *
     * @package caspar
     * @subpackage core
     */
    class CreateTables extends CliCommand
    {

        protected function _setup()
        {
            $this->_command_name = 'create_tables';
            $this->_description = "Creates all database tables found in entities";
            $this->addOptionalArgument('table', 'Only create this specific table');
        }

        public function do_execute()
        {
            $this->cliEcho("\n");
            $table = $this->getProvidedArgument('table');
            if ($table) {
                $this->cliEcho('Looking for the ');
                $this->cliEcho($table, 'yellow', 'bold');
                $this->cliEcho(' entity table in ');
            } else {
                $this->cliEcho('Creating tables from all entity tables in ');
            }
            $this->cliEcho('application' . DS . 'entities' . DS . 'tables', 'white', 'bold');
            $this->cliEcho("\n");

            $iterator = new \DirectoryIterator(CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'tables');
            foreach ($iterator as $fileinfo)
            {
                if ($fileinfo->isDir())
                {
                    continue;
                }

                $tablefile = $fileinfo->getFilename();
                if (($tablename = mb_substr($tablefile, 0, mb_strpos($tablefile, '.'))) != '')
                {
                    if ($table && $table != $tablename) {
                        continue;
                    }

                    $this->cliEcho('* creating ', 'green');
                    $this->cliEcho($tablename, 'yellow', 'bold');

                    $tablename = "\\application\\entities\\tables\\{$tablename}";
                    $reflection = new \ReflectionClass($tablename);
                    $docblock = $reflection->getDocComment();
                    $annotationset = new \b2db\AnnotationSet($docblock);
                    if ($annotationset->hasAnnotation('Table'))
                    {
                        \b2db\Core::getTable($tablename)->drop();
                        \b2db\Core::getTable($tablename)->create();
                        \b2db\Core::getTable($tablename)->createIndexes();
                    }

                    $this->cliEcho("\n");
                }
            }

            $this->cliEcho("\n");
            $this->cliEcho("Done!", 'green');
            $this->cliEcho("\n");
        }

    }
