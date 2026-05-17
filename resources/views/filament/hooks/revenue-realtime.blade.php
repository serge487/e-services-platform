@include('partials.broadcasting')
<script>
    window.__notificationUserId = {{ auth()->id() }};
</script>
@vite(['resources/js/admin-reverb.js'])
