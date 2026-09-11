 <!-- DataTables -->
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
 <!-- DataTables  & Plugins -->
 <script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
 <script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

 <div class="modal fade" id="modaldatapelanggan" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="staticBackdropLabel">Cari Data Pelanggan</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <table id="datapelanggan" class="table table-bordered table-hover dataTable dtr-inline collapsed">
                     <thead>
                         <tr>
                             <th style="width: 5%;">No</th>
                             <th>Nama Pelanggan</th>
                             <th>PIC</th>
                             <th>Email</th>
                             <th>Alamat</th>
                             <th>No Telp / Handphone</th>
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

 <!-- Modal Edit Pelanggan -->
 <div class="modal fade" id="modalEditPelanggan" tabindex="-1" aria-labelledby="modalEditPelangganLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="modalEditPelangganLabel">Edit Pelanggan</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <form id="formEditPelanggan">
                     <input type="hidden" name="id_pelanggan" id="editIdPelanggan">
                     <div class="form-group">
                         <label for="editNamaPelanggan">Nama Pelanggan</label>
                         <input type="text" class="form-control" name="editNamaPelanggan" id="editNamaPelanggan">
                         <span class="invalid-feedback erroreditNamaPelanggan" id="erroreditNamaPelanggan"></span>
                     </div>
                     <div class="form-group">
                         <label for="editNamaPic">Nama PIC</label>
                         <input type="text" class="form-control" name="editNamaPic" id="editNamaPic">
                         <span class="invalid-feedback errorEditNamaPic"></span>
                     </div>
                     <div class="form-group">
                         <label for="editEmail">Email</label>
                         <input type="email" class="form-control" name="editEmail" id="editEmail">
                         <span class="invalid-feedback errorEditEmail"></span>
                     </div>
                     <div class="form-group">
                         <label for="editAlamat">Alamat</label>
                         <textarea class="form-control" name="editAlamat" id="editAlamat" rows="3"></textarea>
                         <span class="invalid-feedback errorEditAlamat"></span>
                     </div>
                     <div class="form-group">
                         <label for="editTelp">No Telp / Handphone</label>
                         <input type="text" class="form-control" name="editTelp" id="editTelp">
                         <span class="invalid-feedback errorEditTelp" id="errorEditTelp"></span>
                     </div>
                     <div class="form-group">
                         <label for="editFax">Fax <small class="text-muted">(opsional, kosongkan kalau tidak ada)</small></label>
                         <input type="text" class="form-control" name="editFax" id="editFax">
                         <span class="invalid-feedback errorEditFax"></span>
                     </div>
                     <div class="form-group">
                         <label for="editTo">To / Bagian Penerima <small class="text-muted">(opsional, kosongkan kalau tidak ada)</small></label>
                         <input type="text" class="form-control" name="editTo" id="editTo" placeholder="Contoh: Bag. Keuangan">
                         <span class="invalid-feedback errorEditTo"></span>
                     </div>
                     <div class="form-group">
                        <label for="editGudang">Gudang</label>

                        <select class="form-control" name="editGudang" id="editGudang">
                            <option value="">-- Pilih Gudang --</option>

                            <?php foreach ($gudang as $item): ?>
                                <option value="<?= esc($item['gdgid']) ?>">
                                    <?= esc($item['gdgnama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <span class="invalid-feedback errorEditGudang" id="errorEditGudang"></span>
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
         $('#datapelanggan').DataTable({
             responsive: true,
             processing: true,
             serverSide: true,
             ajax: '<?= site_url('pelanggan/listData') ?>',
             order: [],
             columns: [{
                     data: 'nomor',
                     orderable: false,
                     className: 'text-center'
                 },
                 {
                     data: 'pelnama',
                     className: 'text-center'
                 },
                 {
                     data: 'pelpic',
                     className: 'text-center'
                 },
                 {
                     data: 'pelemail',
                     className: 'text-center'
                 },
                 {
                     data: 'pelalamat',
                     className: 'text-left'
                 },
                 {
                     data: 'peltelp',
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
         $('#namapelanggan').val(nama);
         $('#idpelanggan').val(id);

         $('#modaldatapelanggan').modal('hide');
     }

     function editData(id, nama, pic, email, alamat, telp, gdgid, fax, to) {
         $.getJSON('/pelanggan/pemakaian', { id: id }).done(function(response) {
             const dipakai = response.pemakaian && response.pemakaian.length > 0;

             if (!dipakai) {
                 bukaEditPelangganModal(id, nama, pic, email, alamat, telp, gdgid, fax, to);
                 return;
             }

             const daftar = response.pemakaian
                 .map(item => `<li><strong>${item.label}</strong> (${item.jumlah} data)</li>`)
                 .join('');
             Swal.fire({
                 title: 'Pelanggan Sedang Digunakan',
                 html: `<p>Pelanggan ini sedang dipakai di data lain. Datanya masih aman diedit.</p><ul style="text-align:left; margin:12px auto 0; width:fit-content;">${daftar}</ul>`,
                 icon: 'info',
                 showCancelButton: true,
                 confirmButtonText: 'Lanjut Edit',
                 cancelButtonText: 'Batal'
             }).then((result) => {
                 if (result.isConfirmed) {
                     bukaEditPelangganModal(id, nama, pic, email, alamat, telp, gdgid, fax, to);
                 }
             });
         }).fail(function() {
             Swal.fire('Kesalahan', 'Informasi pemakaian pelanggan gagal dimuat.', 'error');
         });
     }

     function bukaEditPelangganModal(id, nama, pic, email, alamat, telp, gdgid, fax, to) {
         $('#editIdPelanggan').val(id);
         $('#editNamaPelanggan').val(nama);
         $('#editNamaPic').val(pic === '-' ? '' : pic);
         $('#editEmail').val(email === '-' ? '' : email);
         $('#editAlamat').val(alamat === '-' ? '' : alamat);
         $('#editTelp').val(telp);
         $('#editFax').val(fax === '-' ? '' : fax);
         $('#editTo').val(to === '-' ? '' : to);
         $('#editGudang').val(gdgid || '');

         // Menampilkan modal edit
         $('#modalEditPelanggan').modal('show');
         $('#modaldatapelanggan').modal('hide');
     }

     function update() {
         var idPelanggan = $('#editIdPelanggan').val();
         var namaPelanggan = $('#editNamaPelanggan').val();
         var namaPic = $('#editNamaPic').val();
         var email = $('#editEmail').val();
         var alamat = $('#editAlamat').val();
         var telp = $('#editTelp').val();
         var fax = $('#editFax').val();
         var to = $('#editTo').val();
         var gudang = $('#editGudang').val();

         $.ajax({
             url: '<?= site_url('pelanggan/update') ?>',
             type: 'POST',
             data: {
                 [csrfToken]: csrfHash,
                 id_pelanggan: idPelanggan,
                 editNamaPelanggan: namaPelanggan,
                 editNamaPic: namaPic,
                 editEmail: email,
                 editAlamat: alamat,
                 editTelp: telp,
                 editFax: fax,
                 editTo: to,
                 editGudang: gudang,
             },
             dataType: 'json',
             success: function(response) {
                 if (response.error) {
                     let err = response.error;

                     if (err.errNamaPelanggan) {
                         $('#editNamaPelanggan').addClass('is-invalid');
                         $('.erroreditNamaPelanggan').html(err.errNamaPelanggan);
                     }
                     if (err.errTelp) {
                         $('#editTelp').addClass('is-invalid');
                         $('.errorEditTelp').html(err.errTelp);
                     }
                     if (err.errNamaPic) {
                        $('#editNamaPic').addClass('is-invalid');
                        $('.errorEditNamaPic').html(err.errNamaPic);
                    }
                     if (err.errEmail) {
                        $('#editEmail').addClass('is-invalid');
                        $('.errorEditEmail').html(err.errEmail);
                    }
                     if (err.errAlamat) {
                        $('#editAlamat').addClass('is-invalid');
                        $('.errorEditAlamat').html(err.errAlamat);
                    }
                     if (err.errFax) {
                        $('#editFax').addClass('is-invalid');
                        $('.errorEditFax').html(err.errFax);
                    }
                     if (err.errTo) {
                        $('#editTo').addClass('is-invalid');
                        $('.errorEditTo').html(err.errTo);
                    }
                     if (err.errGudang) {
                        $('#editGudang').addClass('is-invalid');
                        $('.errorEditGudang').html(err.errGudang);
                    }

                 } else if (response.sukses) {
                     // Menampilkan pesan sukses dengan Swal.fire
                     Swal.fire({
                         icon: 'success',
                         title: 'Update Data',
                         text: response.sukses
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $('#modalEditPelanggan').modal('hide');
                             $('#datapelanggan').DataTable().ajax.reload();
                             $('#modaldatapelanggan').modal('show');
                         }
                     });
                 }
             }
         });
     }

     function hapus(id, nama) {
         Swal.fire({
             title: 'Hapus Pelanggan ?',
             text: "Yakin menghapus Data Pelanggan dengan nama" + nama + "?",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#3085d6',
             cancelButtonColor: '#d33',
             confirmButtonText: 'Ya, Hapus !'
         }).then((result) => {
             if (result.isConfirmed) {
                 $.ajax({
                     type: "post",
                     url: '<?= site_url('pelanggan/hapus') ?>',
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

                             $('#datapelanggan').DataTable().ajax.reload();
                         } else if (response.error) {
                             Swal.fire({
                                 icon: 'error',
                                 title: 'Gagal',
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
