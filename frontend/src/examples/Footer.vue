<template>
  <footer class="py-3 footer">
    <div class="container-fluid">
      <div class="row align-items-center justify-content-center">
        <div class="col-auto">
          <div class="text-sm text-center copyright text-muted">
            © {{ new Date().getFullYear() }} Siteuri PRO
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: "AppFooter",
};
</script>
