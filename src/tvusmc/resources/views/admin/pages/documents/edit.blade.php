@extends('admin.master')

@section('head')
<style>
    #ckeditor {
        width: 100%;
        max-width: 960px;
        border: 1px solid #ccc;
    }
</style>
@endsection

@section('content')
    <script src="/ckeditor5/ckeditor.js"></script>
    <script src="/assets/ckfinder/ckfinder.js"></script>

    <div class="row">
        <div class="col-sm-12">
            <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                   <div class="iq-header-title">
                      <h4 class="card-title">Chỉnh sửa văn bản</h4>
                   </div>
                </div>
                <div class="iq-card-body">
                   <form method="POST" id="form-document" enctype="multipart/form-data">
                        <div class="row">

                            <div class="form-group col-sm-12 col-lg-9">
                                <label for="title">Tiêu đề văn bản</label>
                                <input type="text" name="title" value="{{ old('title') ? old('title') : $document->title }}" class="form-control" id="title" required>
                            </div>

                            <div class="form-group col-sm-12 col-lg-3">
                                <label>Ngày đăng</label>
                                <input type="datetime" id="timepicker" placeholder="Giờ:phút ngày/tháng/năm" class="form-control" value="{{ old('time-post') ? old('time-post') : date('d/m/Y', strtotime($document->created_at)) }}"  name="time-post" required>
                            </div>

                             <div class="form-group col-sm-12 col-md-8">
                                <textarea name="content" id="inputContent" hidden required>{!!$document->content!!}</textarea>
                                <label>Nội dung</label>
                                <div id="ckeditor">{!!$document->content!!}</div>
                             </div>

                             <div class="form-group col-sm-12 col-md-4">
                                <label for="">File đính kèm (1 hoặc nhiều)</label>
                                <div id="file-list" style="font-size: 1rem">
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file-input">
                                    <label class="custom-file-label" for="customFile">Chọn file</label>
                                </div>
                             </div>
                             @php
                                 $fileDocuments = [];
                                 foreach ($document->files as $file) {
                                    array_push($fileDocuments, [
                                        'fileName' => $file['filename'],
                                        'url' => $file['file_url'],
                                    ]);
                                 }
                             @endphp
                             <input type="text" name="files" id="files-upload" hidden value="{{ json_encode($fileDocuments) }}">
                        </div>
                        <div class="checkbox mb-3">
                            {{-- <label><input type="checkbox" name="post-now"> Đăng ngay</label> --}}
                        </div>
                        <button class="btn btn-primary">Lưu</button>
                        @csrf
                   </form>
                </div>
             </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        const fileList = JSON.parse(document.getElementById('files-upload').value);

        document.getElementById('file-input').addEventListener('change', (e) => {
            const formData = new FormData();
            formData.append('upload', e.target.files[0]);

            fetch('/api/upload', {
                method: "POST",
                body: formData
            }).then((res) => res.json())
            .then(res => {
                fileList.push(res);
                handleRenderListFile(fileList);
                document.getElementById('files-upload').value = JSON.stringify(fileList);
            })
            .catch((err) => {console.log(err)})
        })

        function deleteFileInFileList(index) {
            fileList.splice(index, 1);
            handleRenderListFile(fileList);
            document.getElementById('files-upload').value = JSON.stringify(fileList);
        }

        function handleRenderListFile(listFile) {
            const html = listFile.map((fileItem, index) => {
                return `<div id="file_1" style="display: flex">
                            <div style="max-width: 90%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">${fileItem.fileName}</div>
                            <i onclick="deleteFileInFileList(${index})" class="ri-close-circle-line text-danger" style="font-size: 1.2rem; cursor: pointer;" title="Xóa"></i>
                        </div>`
                }).join('');
            document.getElementById('file-list').innerHTML = html;
        }
        handleRenderListFile(fileList);

        BalloonEditor.create(document.querySelector("#ckeditor"), {
        mediaEmbed: {
          previewsInData: true,
        },
        placeholder: "Soạn nội dung...",
        ckfinder: {
          uploadUrl: "/api/upload",
          options: {
            resourceType: "Images",
          },
        },
      })
        .then((editor) => {
          editor.model.document.on("change:data", () => {
            document.getElementById('inputContent').value = editor.getData()
          });
        })
        .catch((err) => {
          console.error(err.stack);
        });

        flatpickr("#timepicker", {
            shorthandCurrentMonth: true,
            ariaDateFormat: "d/m/Y",
            allowInput: true,
            altInput: true,
            altFormat: "d/m/Y",
            dateFormat: "d/m/Y",
            time_24hr: true,
            defaultHour: 7,
            locale: 'vn',
            disableMobile: true,
        });
    </script>

@endsection
