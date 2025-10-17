import Swal from "sweetalert2";

export default {
  methods: {
    showSwal(options) {
      new Swal({
        toast: true,
        position: "top-right",
        iconColor: "white",
        width: options.width ? options.width : 300,
        text: options.message,
        customClass: {
          popup: options.type === "success" ? "bg-success" : "bg-danger",
          htmlContainer: "text-white",
        },
        showConfirmButton: false,
        showCloseButton: true,
        timer: 2000,
        timerProgressBar: true,
      });
    },
    showSwalConfirmationDelete() {
      const swalDelete = new Swal({
        title: "Ești sigur?",
        text: "Nu vei putea anula această acțiune!",
        showCancelButton: true,
        confirmButtonText: "Da, șterge!",
        cancelButtonText: "Nu, anulează!",
        reverseButtons: true,
        customClass: {
          confirmButton: "btn bg-gradient-success",
          cancelButton: "btn bg-gradient-danger",
        },
        buttonsStyling: false,
        heightAuto: false,
        backdrop: true,
      });

      return swalDelete;
    },
  },
};
