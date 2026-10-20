<?php

namespace App\Livewire;

use App\Models\Empresa;
use Livewire\Component;
use App\Livewire\Form\EmpresaForm;

class LivewireEmpresaForm extends Component
{
    public EmpresaForm $form;

    public function mount()
    {
        $company = Empresa::first();

        if ($company) {
            $this->form->fillForm($company);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'nit' => $this->form->nit,
                'razon_social' => $this->form->razon_social,
                'nombre_comercial' => $this->form->nombre_comercial ?: null,
                'actividad_economica' => $this->form->actividad_economica ?: null,
                'email' => $this->form->email ?: null,
                'telefono' => $this->form->phone ?: null,
                'direccion' => $this->form->address ?: null,
                'codigo_sistema' => $this->form->codigo_sistema ?: null,
                'numero_solicitud' => $this->form->numero_solicitud ?: null,
                'cuis' => $this->form->cuis ?: null,
                'ambiente' => $this->form->ambiente,
                'soap_version' => $this->form->soap_version,
                'wsdl_sincronizacion' => $this->form->wsdl_sincronizacion ?: null,
                'wsdl_operaciones' => $this->form->wsdl_operaciones ?: null,
                'wsdl_codigos' => $this->form->wsdl_codigos ?: null,
                'wsdl_factura_compra_venta' => $this->form->wsdl_factura_compra_venta ?: null,
                'estado' => 1,
            ];

            if ($this->form->id) {
                $company = Empresa::findOrFail($this->form->id);
                $company->update($data);
            } else {
                $company = Empresa::create($data);
                $this->form->id = $company->id;
            }

            $this->dispatch('toast', type: 'success', message: 'Datos de la empresa guardados correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudieron guardar los datos de la empresa.');
        }
    }

    public function render()
    {
        return view('livewire.empresa.form');
    }
}
