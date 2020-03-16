<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model repository';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $repositoryClass;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $model;

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->setRepositoryClass();
        $result = parent::handle();
        Artisan::call('make:repositoryinterface', ['name' => $this->model]);
        Artisan::call('make:model', ['name' => '\Shop\\' . $this->model . 's\\' . $this->model]);
    }

    /**
     * Set repository class name
     *
     * @return  void
     */
    private function setRepositoryClass()
    {
        // $name = ucwords(strtolower($this->argument('name')));
        $name = $this->argument('name');
        if (strpos($name, $this->type) !== false) {
            $name = substr($name, 0, strlen($name) - strlen($this->type));
        }
        $this->model = $name;

        $this->repositoryClass = $name . $this->type . 's';

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        if (!$this->argument('name')) {
            throw new InvalidArgumentException("Missing required argument model name");
        }

        $stub = parent::replaceClass($stub, $name);
        
        return str_replace(['Dummies', 'Dummy', 'dummy'], [$this->model . 's', $this->model, strtolower($this->model)], $stub);
    }

    /**
     * 
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return  base_path('./app/Console/Stubs/DummyRepository.stub');
    }
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Shop\/' . $this->model . 's\Repositories';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model class.'],
        ];
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name')) . $this->type;
    }
}
