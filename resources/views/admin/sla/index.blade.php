@extends('layouts.master')

@section('title', 'SLA')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>SLA</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">SLA</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Configurations SLA</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Actif</th>
                                    <th>Validation</th>
                                    <th>Expiration</th>
                                    <th>Annulation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($configs as $config)
                                    <tr>
                                        <td>{{ $config->name }}</td>
                                        <td>{{ $config->is_active ? 'Oui' : 'Non' }}</td>
                                        <td>{{ $config->validation_minutes }}m</td>
                                        <td>{{ $config->expiration_minutes }}m</td>
                                        <td>{{ $config->cancellation_minutes }}m</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">Aucune configuration SLA.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $configs->links() }}
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Regles SLA</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Configuration</th>
                                    <th>Nom</th>
                                    <th>Actif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rules as $rule)
                                    <tr>
                                        <td>{{ optional($rule->config)->name ?? '-' }}</td>
                                        <td>{{ $rule->name }}</td>
                                        <td>{{ $rule->is_active ? 'Oui' : 'Non' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">Aucune regle SLA.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $rules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
