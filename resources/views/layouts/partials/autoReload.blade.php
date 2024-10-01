<div class="d-inline">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="auto-reload" {{ (session('auto_reload') ?? false) ? 'checked' : '' }}>
        <label class="custom-control-label" for="auto-reload">{{ __('common.Auto_Reload') }}</label>
    </div>
</div>

<script>
    let autoReload = document.getElementById('auto-reload');
    let timer = null;

    if (autoReload.checked) {
        timer = setInterval(function() {
            window.location.reload();
        }, 15000);
    }

    autoReload.addEventListener('change', function() {
        if (this.checked) {
            timer = setInterval(function() {
                window.location.reload();
            }, 5000);
        } else {
            clearInterval(timer);
        }

        axios.post('{{ request()->url() }}', { auto_reload: this.checked })
            .then(function (response) {
                console.log(response);
            })
            .catch(function (error) {
                console.log(error);
            });
    });
</script>

