<?php
require_once ("enrollCourseStudentFunction.php");
?>

<?php require_once ("../components/header.php"); ?>

<head>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once ("../components/sidebar.php"); ?>

        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php require_once ("../components/topbar.php"); ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Enroll Mata Kuliah - "Kode MK"</h1>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <form action="enrollCourseStudent.php" method="get">
                                    <input type="hidden" name="CourseId" value="<?= $_GET['CourseId']; ?>">
                                    <div class="form-group row">
                                        <label for="inputTahunAkt" class="col-xl-4 col-form-label">Tahun
                                            Angkatan</label>
                                        <div class="col-xl-8">
                                            <input type="number" class="form-control" id="inputTahunAkt"
                                                name="tahunAngkatan" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputNamaMhs" class="col-xl-4 col-form-label">Nama Mahasiswa</label>
                                        <div class="col-xl-8">
                                            <input type="text" class="form-control" id="inputNamaMhs"
                                                name="namaMahasiswa" />
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success tambah_btn" id="btnCari">Cari</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    &nbsp;

                    <!-- Tabel Mata Kuliah -->
                    <table id="example" class="display cell-border" style="width: 100%">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Tahun Angkatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data["students"] as $student): ?>
                            <tr>
                                <td>
                                    <?= $student["StudentId"] ?>
                                </td>
                                <td>
                                    <?= $student["Name"] ?>
                                </td>
                                <td>
                                    <?= $student["YearIn"] ?>
                                </td>

                                <?php if ($student["EnrollmentStatus"] == 1): ?>

                                <td style="display: flex; gap: 5px">

                                    <a class="btn btn-danger btn-sm"
                                        href="unenrollFunction.php?enrollmentId=<?= $student["EnrollmentId"] ?>"
                                        style="width: 90px; color: white;">Unenroll</a>
                                </td>
                                <?php else: ?>

                                <td style="display: flex; gap: 5px">
                                    <a class="btn btn-warning btn-sm"
                                        href="enrollFunction.php?StudentId=<?= $student["StudentId"] ?>&CourseId=<?= $_GET["CourseId"] ?>"
                                        style="width: 90px; color: white;">Enroll</a>
                                </td>
                                <?php endif; ?>

                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <br>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2020</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <?php require_once ("../components/js.php"); ?>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script>
    new DataTable('#example', {
        responsive : true,
        columns: [{
                data: 'kode_MK'
            }, {
                data: 'mata_kuliah'
            }, {
                data: 'nama_mhs'
            },
            {
                data: 'aksi'
            }
        ]
    });
    </script>

</body>

</html>
