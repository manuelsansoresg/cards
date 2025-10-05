@extends('admin.layout')

@section('content')
<style>
    .stat-card{border:1px solid rgba(0,0,0,.06);border-radius:.85rem;transition:.2s;overflow:hidden;background:#fff}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 1rem 2rem rgba(0,0,0,.08)}
    .stat-icon{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff}
    .bg-users{background:linear-gradient(45deg,#2d6cdf,#3f8bff)}
    .bg-uploads{background:linear-gradient(45deg,#06b6d4,#22d3ee)}
    .bg-categories{background:linear-gradient(45deg,#8b5cf6,#a78bfa)}
    .bg-orders{background:linear-gradient(45deg,#ef4444,#f97316)}
    .stat-value{font-weight:700;font-size:2rem}
    .stat-title{font-weight:600}
    .stat-link{text-decoration:none;color:inherit}
    .stat-link:focus{outline:2px solid #3f8bff;outline-offset:2px;border-radius:.85rem}
</style>

<div class="row g-4">
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.users.index') }}" class="stat-link">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-users"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="stat-title">Usuarios</div>
                        <div class="stat-value">{{ $stats['users'] }}</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.uploads.index') }}" class="stat-link">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-uploads"><i class="fas fa-images"></i></div>
                    <div>
                        <div class="stat-title">Tarjetas</div>
                        <div class="stat-value">{{ $stats['uploads'] }}</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.categorias.index') }}" class="stat-link">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-categories"><i class="fas fa-tags"></i></div>
                    <div>
                        <div class="stat-title">Categorías</div>
                        <div class="stat-value">{{ $stats['categorias'] }}</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.ordenes.index') }}" class="stat-link">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-orders"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="stat-title">Órdenes</div>
                        <div class="stat-value">{{ $stats['ordenes'] }}</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection