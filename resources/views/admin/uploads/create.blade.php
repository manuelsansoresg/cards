@extends('admin.layout')

@section('content')
<h4>Nueva tarjeta</h4>

<form method="POST" action="{{ route('admin.uploads.store') }}" class="mt-3" enctype="multipart/form-data">
    @csrf
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Título</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Precio (USD)</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="0.00" value="{{ old('price') }}">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Costo en stars</label>
            <div id="starsCostDisplay" class="form-control bg-light" style="height:38px; display:flex; align-items:center;">{{ old('stars_cost', 0) }}</div>
            <input type="hidden" name="stars_cost" id="stars_cost" value="{{ old('stars_cost', 0) }}">
            <small class="text-muted">1 USD equivale a {{ optional(\App\Models\StarsSetting::first())->stars_per_dollar ?? 1 }} stars</small>
        </div>
        <div class="col-md-3">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="is_free" id="is_free" {{ old('is_free') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_free">Gratuito</label>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-select" required>
                <option value="">-- Seleccionar --</option>
                @foreach($categorias as $c)
                    <option value="{{ $c->id }}" {{ old('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Archivos (múltiple)</label>
            <input type="file" name="media_files[]" class="form-control" multiple required>
            <small class="text-muted">Se guardarán en <code>public/uploads/images</code> o <code>public/uploads/videos</code> según el tipo detectado</small>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success" type="submit">Crear</button>
        <a href="{{ route('admin.uploads.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</form>
<script>
    (function(){
        const priceEl = document.getElementById('price');
        const isFreeEl = document.getElementById('is_free');
        const starsDisplayEl = document.getElementById('starsCostDisplay');
        const starsHiddenEl = document.getElementById('stars_cost');
        const starsPerDollar = {{ (int)(optional(\App\Models\StarsSetting::first())->stars_per_dollar ?? 1) }};

        function updateStars(){
            const isFree = isFreeEl.checked;
            if (isFree){
                priceEl.value = '';
                priceEl.setAttribute('disabled','disabled');
                starsDisplayEl.textContent = '0';
                starsHiddenEl.value = 0;
                return;
            }
            priceEl.removeAttribute('disabled');
            const price = parseFloat(priceEl.value || '0');
            const stars = Math.round(price * starsPerDollar);
            starsDisplayEl.textContent = isNaN(stars) ? '0' : String(stars);
            starsHiddenEl.value = isNaN(stars) ? 0 : stars;
        }

        priceEl && priceEl.addEventListener('input', updateStars);
        isFreeEl && isFreeEl.addEventListener('change', updateStars);
        updateStars();
    })();
</script>
@endsection