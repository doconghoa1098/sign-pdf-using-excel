<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign PDF using Excel</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" type="text/css" />
</head>

<body>
    <div>
        <form id="formSign" method="POST">
            @csrf
            <input type="file" name="pdf_file" id="input-pdf" accept=".pdf">
            <input type="file" name="excel_file" id="input-excel" accept=".xlsx, .xls, .csv">
            <button id="pdf-btn">Choose PDF</button>
            <button id="excel-btn">Choose Excel</button>
            <button id="save-btn">Save</button>
        </form>
    </div>
    <div id="pdfPreviewContainer">
        <div id="pageOne"></div>
        <div id="pdfPagesContainer"></div>
    </div>

    <div id="loader-overlay">
        <div class="custom-loader"></div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/pdf.min.mjs') }}" type="module"></script>
    <script type="module">
        $(document).ready(function () {
            const pdfBtn = $('#pdf-btn');
            const excelBtn = $('#excel-btn');
            const saveBtn = $('#save-btn');
            const filePdf = $('#input-pdf');
            const fileExcel = $('#input-excel');
            const pdfPagesContainer = $('#pdfPagesContainer');

            pdfBtn.on('click', function(e) {
                e.preventDefault()
                filePdf.click()
            });

            excelBtn.on('click',  function(e) {
                e.preventDefault()
                fileExcel.click()
            });

            saveBtn.click(function (e) {
                e.preventDefault()
                let data = new FormData($('#formSign')[0]);
                if (isFormValid()) {
                    $.ajax({
                        url: "{{ route('api.sign.pdf') }}",
                        method: 'POST',
                        processData: false,
                        contentType: false,
                        cache: false,
                        data: data,
                        beforeSend: function() {
                            $('#loader-overlay').show();
                        },
                        complete: function() {
                            $('#loader-overlay').hide();
                        },
                        success: function (res) {
                            if (res.status && res.pdfFiles.length > 0) {
                                downloadPdfFiles(res.pdfFiles);
                                $('#formSign')[0].reset();
                            } else {
                                console.log("Excel file error");
                            }
                        },
                        error: function (error) {
                            console.log(error.responseJSON.message);
                        }
                    });
                }
            });

            function downloadPdfFiles(pdfFiles) {
                var url = window.location.protocol + '//' + window.location.host;
                pdfFiles.forEach(function (pdfFile) {
                    var pdfPath = url + '/' + pdfFile;
                    var link = document.createElement('a');
                    link.href = pdfPath;
                    link.target = '_blank';
                    link.download = pdfPath.split('/').pop();
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });

                $.ajax({
                    url: "{{ route('api.delete.pdf') }}",
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { pdfFiles }
                });
            }

            function isFormValid() {
                var selectedFilePdf = filePdf[0].files[0];    
                var selectedFileExcel = fileExcel[0].files[0];
                if (!selectedFilePdf || !selectedFileExcel) {
                    return false;
                }
                return true;
            }

            var { pdfjsLib } = globalThis;
            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.mjs') }}";

            filePdf.on('change', function () {
                pdfPagesContainer.empty();
                const selectedFile  = filePdf[0].files[0];
                if (selectedFile) {
                    const fileReader = new FileReader();
                    fileReader.onload = function () {
                        const typedarray = new Uint8Array(this.result);

                        pdfjsLib.getDocument({ data: typedarray }).promise.then(function (pdf) {
                            $('#pageOne').hide();
                            const numPages = pdf.numPages;
                            for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                                pdf.getPage(pageNum).then(function (page) {
                                    const canvas = document.createElement('canvas');
                                    const context = canvas.getContext('2d');

                                    const viewport = page.getViewport({ scale: 1 });
                                    canvas.width = viewport.width;
                                    canvas.height = viewport.height;

                                    const renderContext = {
                                        canvasContext: context,
                                        viewport: viewport,
                                    };

                                    page.render(renderContext).promise.then(function () {
                                        pdfPagesContainer.append(canvas);
                                    });
                                });
                            }
                        }).catch(function (error) {
                            $('#pageOne').show();
                        });
                    };
                    fileReader.readAsArrayBuffer(selectedFile);
                }
            });

        });
    </script>
</body>

</html>