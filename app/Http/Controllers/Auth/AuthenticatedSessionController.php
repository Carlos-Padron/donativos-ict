<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $this->obtenerPermisosUsuario($request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function obtenerPermisosUsuario($request)
    {
        $oPerfiles = \App\Models\RH\HistorialPuestos::select('TblP.Perfiles')
            ->join('DbIntranet.TblPuestos AS TblP', function($join){
                $join->on('TblHistorialPuestos.TblPP_id', '=', 'TblP.id')
                    ->whereNull('TblP.deleted_at');
            })
            ->where('TblHistorialPuestos.TblDGP_id', Auth::user()->TblDGP_id)
            ->where('TblHistorialPuestos.TblPF_id', 0)
            ->get();

        $aPermisos = [];
        foreach ($oPerfiles as $oPerfil) {
            $aPerfiles = explode(',', $oPerfil->Perfiles);

            $oPermisos = \App\Models\Admin\Perfil::select('Permisos')
                ->whereIn('id', $aPerfiles)
                ->get();

            $aTempPermisos = [];
            foreach ($oPermisos as $oPermiso) {
                $aTempPermisos[] = $oPermiso->Permisos;
            }

            $aPermisos[] = implode(',', $aTempPermisos);
        }

        /*
         * La lista de permisos resultante se une en una sola cadena, que posteriormente se separa
         * por comas para quedarnos al final con los IDs únicos, en caso de que los perfiles asignados
         * tengan permisos repetidos.
         */
        $aPermisos = array_unique(explode(',', implode(',', $aPermisos)));

        $aPermisos = array_map( function( $id ){ return abs( (int) ( $id ?? 0 ) ); }, $aPermisos);

        $request->session()->put([ 'permisos' => ','.implode(',', $aPermisos).',' ]);
        $request->session()->put([ 'aPermisos' => $aPermisos ]);
    }
}
