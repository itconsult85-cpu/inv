 <!-- DataTables -->
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
 <!-- DataTables  & Plugins -->
 <script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

 <div class="modal fade" id="modaldatasupplier" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="staticBackdropLabel">Cari Data Supplier</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <table id="datasupplier" class="table table-bordered table-hover dataTable dtr-inline collapsed">
                     <thead>
                         <tr>
                             <th style="width: 5%;">No</th>
                             <th>Supplier</th>
                             <th>Nama PIC</th>
                             <th>Email</th>
                             <th>No Telp / HP</th>
                             <th>Alamat</th>
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

 <!-- Modal Edit Supplier -->
 <div class="modal fade" id="modalEditSupplier" tabindex="-1" aria-labelledby="modalEditSupplierLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="modalEditSupplierLabel">Edit Supplier</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <form id="formEditSupplier">
                     <input type="hidden" name="id_supplier" id="editIdSupplier">
                     <div class="form-group">
                         <label for="editNamaSupplier">Nama Supplier</label>
                         <input type="text" class="form-control" name="editNamaSupplier" id="editNamaSupplier">
                         <span class="invalid-feedback errorEditNamaSupplier" id="errorEditNamaSupplier"></span>
                     </div>
                     <div class="form-group">
                         <label for="editNamaPic">Nama PIC</label>
                         <input type="text" class="form-control" name="editNamaPic" id="editNamaPic">
                         <span class="invalid-feedback errorEditNamaPic" id="errorEditNamaPic"></span>
                     </div>
                     <div class="form-group">
                         <label for="editEmail">Email</label>
                         <input type="email" class="form-control" name="editEmail" id="editEmail">
                         <span class="invalid-feedback errorEditEmail" id="errorEditEmail"></span>
                     </div>
                     <div class="form-group">
                         <label for="editTelp">No Telp / Handphone</label>
                         <input type="text" class="form-control" name="editTelp" id="editTelp">
                         <span class="invalid-feedback errorEditTelp" id="errorEditTelp"></span>
                     </div>
                     <div class="form-group">
                         <label for="editAlamat">Alamat</label>
                         <textarea class="form-control" name="editAlamat" id="editAlamat"></textarea>
                         <span class="invalid-feedback errorEditAlamat" id="errorEditAlamat"></span>
                     </div>
                     <button type="button" class="btn btn-primary" onclick="update()">Update</button>
                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
             </div>
         </div>
     </div>
 </div>

 <script>
     $(document).ready(function() {
         $('#datasupplier').DataTable({
             responsive: true,
             processing: true,
             serverSide: true,
             ajax: '<?= site_url('supplier/listData') ?><?= !empty($excludeInternal) ? '?exclude_internal=1' : '' ?>',
             order: [],
             columns: [{
                     data: 'nomor',
                     orderable: false,
                     className: 'text-center'
                 },
                 {
                     data: 'supnama',
                     className: 'text-center'
                 },
                 {
                     data: 'suppic',
                     className: 'text-center'
                 },
                 {
                     data: 'supemail',
                     className: 'text-center',
                     render: function(data) {
                         return data || '-';
                     }
                 },
                 {
                     data: 'suptelp',
                     className: 'text-center'
                 },
                 {
                     data: 'alamat',
                     orderable: false,
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
         $('#namasupplier').val(nama);
         $('#idsupplier').val(id);

         $('#modaldatasupplier').modal('hide');
     }

     function editData(id, nama, pic, email, telp, alamat) {
         $.getJSON('<?= site_url('supplier/pemakaian') ?>', { id: id }).done(function(response) {
             if (response.pemakaian && response.pemakaian.length > 0) {
                 $('#editNamaSupplier').prop('readonly', true);
             } else {
                 $('#editNamaSupplier').prop('readonly', false);
             }

             bukaEditSupplierModal(id, nama, pic, email, telp, alamat);
         }).fail(function() {
             showBootstrapModal('Kesalahan', 'Informasi pemakaian supplier gagal dimuat.', 'error');
         });
     }

     function bukaEditSupplierModal(id, nama, pic, email, telp, alamat) {
         $('#editIdSupplier').val(id);
         $('#editNamaSupplier').val(nama);
         $('#editNamaPic').val(pic);
         $('#editEmail').val(email);
         $('#editTelp').val(telp);
         $('#editAlamat').val(alamat);

         // Menampilkan modal edit
         $('#modalEditSupplier').modal('show');
         $('#modaldatasupplier').modal('hide');
     }

     function update() {
         var idSupplier = $('#editIdSupplier').val();
         var namaSupplier = $('#editNamaSupplier').val();
         var namaPic = $('#editNamaPic').val();
         var email = $('#editEmail').val();
         var telp = $('#editTelp').val();
         var alamat = $('#editAlamat').val();

         $.ajax({
             url: '<?= site_url('supplier/update') ?>',
             type: 'POST',
             data: {
                 [csrfToken]: csrfHash,
                 id_supplier: idSupplier,
                 editNamaSupplier: namaSupplier,
                 editNamaPic: namaPic,
                 editEmail: email,
                 editTelp: telp,
                 editAlamat: alamat
             },
             dataType: 'json',
             success: function(response) {
                 if (response.error) {
                     let err = response.error;

                     if (err.errNamaSupplier) {
                         $('#editNamaSupplier').addClass('is-invalid');
                         $('.errorEditNamaSupplier').html(err.errNamaSupplier);
                     }
                     if (err.errNamaPic) {
                         $('#editNamaPic').addClass('is-invalid');
                         $('.errorEditNamaPic').html(err.errNamaPic);
                     }
                     if (err.errEmail) {
                         $('#editEmail').addClass('is-invalid');
                         $('.errorEditEmail').html(err.errEmail);
                     }
                     if (err.errTelp) {
                         $('#editTelp').addClass('is-invalid');
                         $('.errorEditTelp').html(err.errTelp);
                     }
                     if (err.errAlamat) {
                         $('#editAlamat').addClass('is-invalid');
                         $('.errorEditAlamat').html(err.errAlamat);
                     }
                 } else if (response.sukses) {
                     // Menampilkan pesan sukses dengan showBootstrapModal
                     showBootstrapModal({
                         icon: 'success',
                         title: 'Update Data',
                         text: response.sukses
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $('#modalEditSupplier').modal('hide');
                             $('#datasupplier').DataTable().ajax.reload();
                             $('#modaldatasupplier').modal('show');
                         }
                     });
                 }
             }
         });
     }

     function hapus(id, nama) {
         showBootstrapModal({
             title: 'Hapus Supplier?',
             text: "Yakin menghapus Data Supplier dengan nama " + nama + "?",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#3085d6',
             cancelButtonColor: '#d33',
             confirmButtonText: 'Ya, Hapus!'
         }).then((result) => {
             if (result.isConfirmed) {
                 $.ajax({
                     type: "post",
                     url: '<?= site_url('supplier/hapus') ?>',
                     data: {
                         [csrfToken]: csrfHash,
                         id: id
                     },
                     dataType: "json",
                     success: function(response) {
                         if (response.sukses) {
                             showBootstrapModal({
                                 icon: 'success',
                                 title: 'Hapus data',
                                 text: response.sukses
                             }).then((result) => {
                                 if (result.isConfirmed) {
                                     listDataSupplier();
                                     $('#datasupplier').DataTable().ajax.reload();
                                     $('#modaldatasupplier').modal('show');
                                 }
                             });
                         } else if (response.error) {
                             showBootstrapModal({
                                 icon: 'error',
                                 title: 'Gagal',
                                html: response.error
                             });
                             listDataSupplier();
                         }
                     },
                     error: function(xhr, ajaxOptions, thrownError) {
                         showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                     }
                 });
             }
         });
     }

     $(document).ready(function() {
         listDataSupplier();
     });
 </script>
