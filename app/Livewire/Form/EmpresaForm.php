<?php

namespace App\Livewire\Form;

use Livewire\Form;

class EmpresaForm extends Form
{
    public $id = null;
    public $nit = '';
    public $razon_social = '';
    public $nombre_comercial = '';
    public $actividad_economica = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $codigo_sistema = '';
    public $numero_solicitud = '';
    public $cuis = '';
    public $ambiente = 'PRUEBA';
    public $soap_version = 'V2';
    public $wsdl_sincronizacion = '';
    public $wsdl_operaciones = '';
    public $wsdl_codigos = '';
    public $wsdl_factura_compra_venta = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.nit' => 'required|string|max:50',
            'form.razon_social' => 'required|string|max:200',
            'form.nombre_comercial' => 'nullable|string|max:150',
            'form.actividad_economica' => 'nullable|string|max:150',
            'form.email' => 'nullable|email|max:150',
            'form.phone' => 'nullable|string|max:50',
            'form.address' => 'nullable|string|max:255',
            'form.codigo_sistema' => 'nullable|string|max:100',
            'form.numero_solicitud' => 'nullable|integer',
            'form.cuis' => 'nullable|string|max:100',
            'form.ambiente' => 'required|in:PRUEBA,PRODUCCION',
            'form.soap_version' => 'required|in:V2,V3',
            'form.wsdl_sincronizacion' => 'nullable|url|max:255',
            'form.wsdl_operaciones' => 'nullable|url|max:255',
            'form.wsdl_codigos' => 'nullable|url|max:255',
            'form.wsdl_factura_compra_venta' => 'nullable|url|max:255',
        ];
    }

    public function messages()
    {
        return [
            'form.nit.required' => 'El NIT es obligatorio.',
            'form.razon_social.required' => 'La razón social es obligatoria.',
            'form.email.email' => 'El correo electrónico no es válido.',
            'form.ambiente.required' => 'Debe seleccionar el ambiente.',
        ];
    }

    public function fillForm($company)
    {
        $this->id = $company->id;
        $this->nit = $company->nit;
        $this->razon_social = $company->razon_social;
        $this->nombre_comercial = $company->nombre_comercial;
        $this->actividad_economica = $company->actividad_economica;
        $this->email = $company->email;
        $this->phone = $company->telefono;
        $this->address = $company->direccion;
        $this->codigo_sistema = $company->codigo_sistema;
        $this->numero_solicitud = $company->numero_solicitud;
        $this->cuis = $company->cuis;
        $this->ambiente = $company->ambiente;
        $this->soap_version = $company->soap_version;
        $this->wsdl_sincronizacion = $company->wsdl_sincronizacion;
        $this->wsdl_operaciones = $company->wsdl_operaciones;
        $this->wsdl_codigos = $company->wsdl_codigos;
        $this->wsdl_factura_compra_venta = $company->wsdl_factura_compra_venta;
        $this->state = $company->estado;
    }
}
