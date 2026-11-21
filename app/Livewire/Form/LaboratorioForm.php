<?php

namespace App\Livewire\Form;

use App\Models\Laboratorio;
use Illuminate\Validation\Rule;
use Livewire\Form;

class LaboratorioForm extends Form
{
    public $id = null;
    public $nombre = '';
    public $tipo = Laboratorio::TIPO_LABORATORIO;
    public $descripcion = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.nombre' => [
                'required',
                'string',
                'max:150',
                function ($attribute, $value, $fail) {
                    if (Laboratorio::claveComparacion($value) === '') {
                        $fail('El nombre no puede quedar vacío.');
                        return;
                    }

                    if ($existing = $this->duplicado()) {
                        $fail('Ya existe "' . $existing->sufijo . '". No repita laboratorios ni marcas.');
                    }
                },
            ],
            'form.tipo' => ['required', Rule::in(array_keys(Laboratorio::TIPOS))],
            'form.descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'form.nombre.required' => 'El nombre del laboratorio o marca es obligatorio.',
            'form.tipo.required' => 'Seleccione si es laboratorio o marca.',
            'form.tipo.in' => 'El tipo seleccionado no es válido.',
        ];
    }

    /**
     * Otro registro con el mismo nombre, comparando sin puntos, comas,
     * espacios ni el prefijo "LAB." ("LAB. SAE" = "LAB,SAE" = "SAE").
     */
    public function duplicado(): ?Laboratorio
    {
        $key = Laboratorio::claveComparacion($this->nombre);

        if ($key === '') {
            return null;
        }

        return Laboratorio::query()
            ->when($this->id, fn ($q) => $q->where('id', '!=', $this->id))
            ->get(['id', 'nombre', 'tipo'])
            ->first(fn ($lab) => Laboratorio::claveComparacion($lab->nombre) === $key);
    }

    /** Nombre como se guarda: mayúsculas y sin el prefijo "LAB.". */
    public function nombreLimpio(): string
    {
        return Laboratorio::limpiarNombre($this->nombre);
    }

    public function resetForm()
    {
        $this->id = null;
        $this->nombre = '';
        $this->tipo = Laboratorio::TIPO_LABORATORIO;
        $this->descripcion = '';
        $this->estado = 1;
    }

    public function fillForm($laboratorio)
    {
        $this->id = $laboratorio->id;
        $this->nombre = $laboratorio->nombre;
        $this->tipo = $laboratorio->tipo ?: Laboratorio::TIPO_LABORATORIO;
        $this->descripcion = $laboratorio->descripcion;
        $this->estado = $laboratorio->estado;
    }
}
