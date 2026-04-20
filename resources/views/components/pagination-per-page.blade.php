@props(['paginator'])

<div class="flex items-center gap-2">
    <label for="per_page" class="text-sm text-gray-700">Tampilkan:</label>
    <select 
        id="per_page" 
        name="per_page" 
        onchange="updatePerPage(this.value)"
        class="px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
    >
        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
    </select>
    <span class="text-sm text-gray-700">baris</span>
</div>

<script>
    function updatePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page'); // Reset ke halaman pertama
        window.location.href = url.toString();
    }
</script>






