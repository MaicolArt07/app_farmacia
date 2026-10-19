<?php

namespace App\Livewire\Form;

use Livewire\Form;

class BranchForm extends Form
{
    public $id = null;
    public $numero_sucursal = 0;
    public $punto_venta = 0;
    public $name = '';
    public $municipio = '';
    public $address = '';
    public $phone = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.numero_sucursal' => 'required|integer|min:0',
            'form.punto_venta' => 'required|integer|min:0',
            'form.name' => 'required|string|max:150',
            'form.municipio' => 'required|string|max:25',
            'form.address' => 'nullable|string|max:255',
            'form.phone' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'form.numero_sucursal.required' => 'El número de sucursal es obligatorio (0 para casa matriz).',
            'form.punto_venta.required' => 'El punto de venta es obligatorio.',
            'form.name.required' => 'El nombre de la sucursal es obligatorio.',
            'form.municipio.required' => 'El municipio es obligatorio (lo exige el XML de facturación).',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->numero_sucursal = 0;
        $this->punto_venta = 0;
        $this->name = '';
        $this->municipio = '';
        $this->address = '';
        $this->phone = '';
        $this->state = 1;
    }

    public function fillForm($branch)
    {
        $this->id = $branch->id;
        $this->numero_sucursal = $branch->numero_sucursal;
        $this->punto_venta = $branch->punto_venta;
        $this->name = $branch->name;
        $this->municipio = $branch->municipio;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
        $this->state = $branch->state;
    }
}
