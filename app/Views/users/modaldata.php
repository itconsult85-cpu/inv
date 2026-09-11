 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
 <script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

 <div class="modal fade" id="modaldatauser" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="staticBackdropLabel">Cari Data User</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <table id="datauser" class="table table-bordered table-hover dataTable dtr-inline collapsed">
                     <thead>
                         <tr>
                             <th style="width: 5%;">No</th>
                             <th>Nama User</th>
                             <th style="width: 20%;">Aksi</th>
                         </tr>
                     </thead>
                     <tbody>

                     </tbody>
                 </table>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
             </div>
         </div>
     </div>
 </div>
 <script>
     $(document).ready(function() {
         $('#datauser').DataTable({
             responsive: true,
             processing: true,
             serverSide: true,
             ajax: '<?= site_url('users/listDataModal') ?>',
             order: [],
             columns: [{
                     data: 'nomor',
                     orderable: false,
                     className: 'text-center'
                 },
                 {
                     data: 'usernama',
                     className: 'text-center'
                 },
                 {
                     data: 'aksi',
                     className: 'text-center',
                     orderable: false
                 },
             ]
         });
     });

     function pilih(id, nama) {
         $('#namauser').val(nama);
         $('#iduser').val(id);

         $('#modaldatauser').modal('hide');
     }

     function editData(id, nama, telp) {
         $('#editIdUser').val(id);
         $('#editNamaUser').val(nama);
         $('#editTelp').val(telp);

         // Menampilkan modal edit
         $('#modalEditUser').modal('show');
         $('#modaldatauser').modal('hide');
     }

     function update() {
         var idUser = $('#editIdUser').val();
         var namaUser = $('#editNamaUser').val();
         var telp = $('#editTelp').val();

         $.ajax({
             url: '<?= site_url('user/update') ?>',
             type: 'POST',
             data: {
                 [csrfToken]: csrfHash,
                 id_user: idUser,
                 editNamaUser: namaUser,
                 editTelp: telp,
             },
             dataType: 'json',
             success: function(response) {
                 if (response.error) {
                     let err = response.error;

                     if (err.errnamaUser) {
                         $('#editNamaUser').addClass('is-invalid');
                         $('.erroreditNamaUser').html(err.errnamaUser);
                     }
                     if (err.errTelp) {
                         $('#editTelp').addClass('is-invalid');
                         $('.errorEditTelp').html(err.errTelp);
                     }
                 } else if (response.sukses) {
                     // Menampilkan pesan sukses dengan Swal.fire
                     Swal.fire({
                         icon: 'success',
                         title: 'Update Data',
                         text: response.sukses
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $('#modalEditUser').modal('hide');
                             $('#datauser').DataTable().ajax.reload();
                             $('#modaldatauser').modal('show');
                         }
                     });
                 }
             }
         });
     }

     function hapus(id, nama) {
         Swal.fire({
             title: 'Hapus User ?',
             text: "Yakin menghapus Data User dengan nama" + nama + "?",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#3085d6',
             cancelButtonColor: '#d33',
             confirmButtonText: 'Ya, Hapus !'
         }).then((result) => {
             if (result.isConfirmed) {
                 $.ajax({
                     type: "post",
                     url: '<?= site_url('users/hapus') ?>',
                     data: {
                         [csrfToken]: csrfHash,
                         id: id
                     },
                     dataType: "json",
                     success: function(response) {
                         if (response.sukses) {
                             Swal.fire({
                                 icon: 'success',
                                 title: 'Hapus data',
                                 text: response.sukses
                             });

                             listDataUser();
                         } else if (response.error) {
                             Swal.fire({
                                 icon: 'error',
                                 title: 'Gagal',
                                 text: response.error
                             });
                         }
                     },
                     error: function(xhr, ajaxOptions, thrownError) {
                         alert(xhr.status + '\n' + thrownError)
                     }
                 });
             }
         })
     }
     $(document).ready(function() {
         listDataUser();
     });
 </script>