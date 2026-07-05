<div x-data="confirmModal()" x-on:open-confirm.window="open($event.detail)">
  <div x-show="show" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6">
      <h3 class="font-semibold text-gray-900 mb-2" x-text="title"></h3>
      <p class="text-sm text-gray-500 mb-6" x-text="message"></p>
      <div class="flex gap-3 justify-end">
        <button @click="show=false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Batal</button>
        <form :action="formAction" method="POST">
          @csrf
          <input type="hidden" name="_method" :value="httpMethod">
          <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg">
            Konfirmasi
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function confirmModal() {
  return {
    show: false,
    title: '',
    message: '',
    formAction: '',
    formMethod: 'POST',
    httpMethod: 'DELETE',
    open(detail) {
      this.title = detail.title || 'Konfirmasi';
      this.message = detail.message || 'Apakah Anda yakin?';
      this.formAction = detail.action;
      this.httpMethod = detail.method || 'DELETE';
      this.show = true;
    }
  }
}
</script>
