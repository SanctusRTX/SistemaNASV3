<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Computadora;

class ComputadoraController extends Controller
{
    /** Listar todas las computadoras (usado por DashboardController) */
    public static function listar()
    {
        return Computadora::orderBy('departamento')->orderBy('nombre')->get();
    }

    /** Guardar nueva computadora */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:100',
            'tipo'             => 'required|string|max:50',
            'operador'         => 'required|string|max:150',
            'departamento'     => 'required|string|max:150',
            'estado'           => 'required|string|max:50',
            'numero_serie'     => 'nullable|string|max:100',
            'codigo_inventario'=> 'nullable|string|max:100',
            'marca'            => 'nullable|string|max:80',
            'modelo'           => 'nullable|string|max:100',
            'procesador'       => 'nullable|string|max:150',
            'ram'              => 'nullable|string|max:50',
            'almacenamiento'   => 'nullable|string|max:100',
            'tarjeta_grafica'  => 'nullable|string|max:150',
            'sistema_operativo'=> 'nullable|string|max:100',
            'cargo_operador'   => 'nullable|string|max:100',
            'direccion_ip'     => 'nullable|ip',
            'direccion_mac'    => ['nullable', 'regex:/^([0-9A-Fa-f]{2}[:\-]){5}[0-9A-Fa-f]{2}$/'],
            'observaciones'    => 'nullable|string',
        ]);

        $computadora = Computadora::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Computadora registrada correctamente.',
            'data'    => $computadora,
        ]);
    }

    /** Actualizar computadora existente */
    public function update(Request $request, $id)
    {
        $computadora = Computadora::findOrFail($id);

        $request->validate([
            'nombre'           => 'required|string|max:100',
            'tipo'             => 'required|string|max:50',
            'operador'         => 'required|string|max:150',
            'departamento'     => 'required|string|max:150',
            'estado'           => 'required|string|max:50',
            'numero_serie'     => 'nullable|string|max:100',
            'codigo_inventario'=> 'nullable|string|max:100',
            'marca'            => 'nullable|string|max:80',
            'modelo'           => 'nullable|string|max:100',
            'procesador'       => 'nullable|string|max:150',
            'ram'              => 'nullable|string|max:50',
            'almacenamiento'   => 'nullable|string|max:100',
            'tarjeta_grafica'  => 'nullable|string|max:150',
            'sistema_operativo'=> 'nullable|string|max:100',
            'cargo_operador'   => 'nullable|string|max:100',
            'direccion_ip'     => 'nullable|ip',
            'direccion_mac'    => ['nullable', 'regex:/^([0-9A-Fa-f]{2}[:\-]){5}[0-9A-Fa-f]{2}$/'],
            'observaciones'    => 'nullable|string',
        ]);

        $computadora->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Computadora actualizada correctamente.',
            'data'    => $computadora->fresh(),
        ]);
    }

    /** Eliminar computadora */
    public function destroy($id)
    {
        $computadora = Computadora::findOrFail($id);
        $computadora->delete();

        return response()->json([
            'success' => true,
            'message' => 'Computadora eliminada correctamente.',
        ]);
    }
}
