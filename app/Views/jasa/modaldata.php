 <!-- DataTables -->
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
 <!-- DataTables  & Plugins -->
 <script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

 <div class="modal fade" id="modaldatajasa" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="staticBackdropLabel">Cari Data jasa</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <table id="datajasa" class="table table-bordered table-hover dataTable dtr-inline collapsed">
                     <thead>
                         <tr>
                             <th style="width: 5%;">No</th>
                             <th>Nama jasa</th>
                             <th>Harga Modal</th>
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

 <!-- Modal Edit jasa -->
 <div class="modal fade" id="modalEditJasa" tabindex="-1" aria-labelledby="modalEditJasaLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="modalEditJasaLabel">Edit Jasa</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <form id="formEditJasa">
                     <input type="hidden" name="id_jasa" id="editIdJasa">
                     <div class="form-group">
                         <label for="editNamaJasa">Nama Jasa</label>
                         <input type="text" class="form-control" name="editNamaJasa" id="editNamaJasa">
                         <span class="invalid-feedback erroreditNamaJasa" id="erroreditNamaJasa"></span>
                     </div>
                     <div class="form-group">
                         <label for="editHargaModal">Harga Modal / Pcs (Rp)</label>
                         <input type="number" min="0" step="0.01" class="form-control" name="editHargaModal" id="editHargaModal" value="0">
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
         $('#datajasa').DataTable({
             responsive: true,
             processing: true,
             serverSide: true,
             ajax: '<?= site_url('jasa/listData') ?>',
             order: [],
             columns: [{
                     data: 'nomor',
                     orderable: false,
                     className: 'text-center'
                 },
                 {
                     data: 'namajasa',
                     className: 'text-center'
                 },
                 {
                     data: 'harga_modal',
                     className: 'text-right'
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
         $('#namajasa').val(nama);
         $('#idjasa').val(id);

         $('#modaldatajasa').modal('hide');
     }

     function editData(id, nama, hargaModal) {
         $.getJSON('/jasa/pemakaian', { id: id }).done(function(response) {
             if (response.pemakaian && response.pemakaian.length > 0) {
                 Swal.fire('Catatan', 'Jasa sudah digunakan. Nama jasa jangan diubah, tapi harga modal masih bisa diperbarui untuk reporting.', 'info');
             }

             bukaEditJasa(id, nama, hargaModal);
         }).fail(function() {
             Swal.fire('Kesalahan', 'Informasi pemakaian jasa gagal dimuat.', 'error');
         });
     }

     function bukaEditJasa(id, nama, hargaModal) {
         $('#editIdJasa').val(id);
         $('#editNamaJasa').val(nama);
         $('#editHargaModal').val(hargaModal || 0);
         //  $('#editTelp').val(telp);

         // Menampilkan modal edit
         $('#modalEditJasa').modal('show');
         $('#modaldatajasa').modal('hide');
     }

     function update() {
         var idJasa = $('#editIdJasa').val();
         var namaJasa = $('#editNamaJasa').val();

         $.ajax({
             url: '<?= site_url('jasa/update') ?>',
             type: 'POST',
             data: {
                 [csrfToken]: csrfHash,
                 id_jasa: idJasa,
                 editNamaJasa: namaJasa,
                 editHargaModal: $('#editHargaModal').val(),
             },
             dataType: 'json',
             success: function(response) {
                 if (response.error) {
                     let err = response.error;

                     if (err.errnamaJasa) {
                         $('#editNamaJasa').addClass('is-invalid');
                         $('.erroreditNamaJasa').html(err.errnamaJasa);
                     }
                 } else if (response.sukses) {
                     // Menampilkan pesan sukses dengan Swal.fire
                     Swal.fire({
                         icon: 'success',
                         title: 'Update Data',
                         text: response.sukses
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $('#modalEditJasa').modal('hide');
                             $('#datajasa').DataTable().ajax.reload();
                             $('#modaldatajasa').modal('show');
                         }
                     });
                 }
             }
         });
     }

     function hapus(id, nama) {
         Swal.fire({
             title: 'Hapus Jasa ?',
             text: "Yakin menghapus Data Jasa dengan nama" + nama + "?",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#3085d6',
             cancelButtonColor: '#d33',
             confirmButtonText: 'Ya, Hapus !'
         }).then((result) => {
             if (result.isConfirmed) {
                 $.ajax({
                     type: "post",
                     url: '<?= site_url('jasa/hapus') ?>',
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

                              $('#datajasa').DataTable().ajax.reload();
                          } else if (response.error) {
                              Swal.fire({
                                  icon: 'error',
                                  title: 'Tidak dapat dihapus',
                                  html: response.error
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
 </script>
