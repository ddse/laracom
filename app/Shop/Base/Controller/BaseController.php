<?php

namespace App\Shop\Base\Controller;

use App\Http\Controllers\Controller;
use App\Shop\Base\BaseFormRequest;
use App\Shop\Base\Services\BaseServiceInterface;
use App\Shop\Products\Requests\CreateProductRequest;
use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;

abstract class BaseController extends Controller
{
    private $service;
    protected $view = 'admin.categories';
    protected $route = '';

    protected abstract function setMiddleware(): array;

    function __construct(BaseServiceInterface $service, $middle = true)
    {
        $view = $this->getNameOfClass();
        $view = str_replace('App\Http\Controllers\\', '', $view);
        $view = substr($view, 0, strrpos($view, '\\'));
        $view = strtolower($view);
        $view = str_replace('\\', '.', $view) . '.';
        $this->service = $service;
        $this->view = $view;
        $this->route = $view;
        if ($middle) {
            $methods = ['create', 'update', 'destroy', 'view', 'delete', 'rollback'];
            foreach ($methods as $method) {
                $only = [];
                switch ($method) {
                    case 'create':
                        $only = ['create', 'store'];
                        break;
                    case 'update':
                        $only = ['edit', 'update'];
                        break;
                    case 'destroy':
                        $only = ['destroy'];
                        break;
                    case 'delete':
                        $only = ['delete'];
                        break;
                    case 'rollback':
                        $only = ['rollback'];
                        break;
                    case 'view':
                    default:
                        $only = ['index', 'show'];
                        break;
                }
                $this->middleware(
                    ['permission:' . $method . '-' . $this->setMiddleware()['permission'] . ', guard:' . $this->setMiddleware()['guard'] . ''],
                    ['only' => $only]
                );
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->view('list', [
            'elements' => $this->service->index()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->view('create', $this->service->create());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateProductRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(BaseFormRequest $request)
    {
        return redirect()
            ->route($this->route . 'edit', $this->service->store($request)->product->id)
            ->with('message', 'Create successful');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        // $product = ;
        return $this->view('show')->with($this->service->show($id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        return $this->view('edit', $this->service->edit($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateProductRequest $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Shop\Products\Exceptions\ProductUpdateErrorException
     */
    public function update(BaseFormRequest $request, int $id)
    {
        $id = $this->service->update($request, $id);
        return redirect()->route($this->route . 'edit', $id)
            ->with('message', 'Update successful');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function delete($id)
    {
        $id = $this->service->delete($id);
        return redirect()->route($this->route . 'index')->with('message', 'Delete successful');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy($id)
    {
        $id = $this->service->delete($id);

        // $customer = $this->customerRepo->findCustomerById($id);

        // $customerRepo = new CustomerRepository($customer);
        // $customerRepo->deleteCustomer();

        return redirect()->route($this->route . 'index')->with('message', 'Delete successful');
    }

    /**
     * @inheritdoc
     * $view custom by base view
     */
    protected function view($view = null, $data = [], $mergeData = [])
    {
        return view($this->view . '.' . $view, $data, $mergeData);
    }

    /**
     * Change base view with param view
     */
    protected function getView($view = null)
    {
        return ($this->view . '.' . $view);
    }

    /**
     * @return string
     */
    public function getNameOfClass()
    {
        return static::class;
    }
}
