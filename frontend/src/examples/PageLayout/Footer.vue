<template>
  <!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <footer class="py-5 footer">
    <div class="container">
      <div class="row">
        <div class="mx-auto mb-4 text-center col-lg-8">
          <a
            href="https://atriaf2.ddns.net/auth_system/login.php"
            target="_blank"
            rel="noopener noreferrer"
            class="mb-2 text-secondary me-xl-5 me-3 mb-sm-0"
          >
            Acces Portal Web Faza 2
          </a>
          <a
            href="https://atriaf3.ddns.net"
            target="_blank"
            rel="noopener noreferrer"
            class="mb-2 text-secondary me-xl-5 me-3 mb-sm-0"
          >
            Acces Portal Web Faza 3
          </a>
        </div>
      </div>
      <div class="row">
        <div class="mx-auto mt-1 text-center col-8">
          <p class="mb-0 text-secondary">
            Copyright © {{ new Date().getFullYear() }} ATRIA - Toate drepturile rezervate.
          </p>
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
