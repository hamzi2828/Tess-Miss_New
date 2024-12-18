@extends('master.master')

<style>
    .form-label {
        font-weight: 500;
        color: #6c757d;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .form-control,
    .form-select {
        border-radius: 0px !important;
    }

    .card-custom {
        border-radius: 5px;
        background-color: #f8f9fa;
    }
</style>

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-2">
        </div>
        <div class="col-md-8">
            <div class="card shadow-lg p-4 card-custom">
                <h4 class="fw-bold text-primary mb-4">Edit Page</h4>

                <form method="POST" action="{{ route('pages.update', $page->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Page Name -->
                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary" for="pageName">Page Name
                            <span class="required-asterisk text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="pageName" name="pageName" value="{{ old('pageName', $page->name) }}" required />
                        @if($errors->has('pageName'))
                            <div class="text-danger">{{ $errors->first('pageName') }}</div>
                        @endif
                    </div>

                    <!-- Page Slug -->
                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary" for="pageSlug">Slug (URL) <span class="required-asterisk text-danger">*</span></label>
                        <input type="text" id="pageSlug" class="form-control" name="pageSlug" value="{{ old('pageSlug', $page->slug) }}" readonly />
                        <small class="text-muted">Example: about-us</small>
                        @if($errors->has('pageSlug'))
                            <div class="text-danger">{{ $errors->first('pageSlug') }}</div>
                        @endif
                    </div>

                    <!-- Page Description -->
                    {{-- <div class="mb-4">
                        <label class="form-label fw-medium text-secondary" for="pageDescription">Description <span class="required-asterisk text-danger">*</span></label>
                        <textarea id="pageDescription" class="form-control" name="pageDescription" rows="5" required>{{ old('pageDescription', $page->description) }}</textarea>
                        @if($errors->has('pageDescription'))
                            <div class="text-danger">{{ $errors->first('pageDescription') }}</div>
                        @endif
                    </div>

                    <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            ClassicEditor
                                .create(document.querySelector('#pageDescription'))
                                .then(editor => {
                                    // Set initial content without additional quotes
                                    editor.setData(`{!! addslashes($page->description) !!}`);

                                    // Sync editor content with textarea for form submission
                                    editor.model.document.on('change:data', () => {
                                        document.querySelector('textarea[name="pageDescription"]').value = editor.getData();
                                    });
                                })
                                .catch(error => {
                                    console.error(error);
                                });
                        });
                    </script> --}}

                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary" for="pageDescription">Description <span class="required-asterisk text-danger">*</span></label>
                        <div id="editor" style="height: 300px;">{!! $page->description !!}</div>
                        <textarea id="pageDescription" name="pageDescription" class="form-control d-none" required>{{ old('pageDescription', $page->description) }}</textarea>
                        @if($errors->has('pageDescription'))
                            <div class="text-danger">{{ $errors->first('pageDescription') }}</div>
                        @endif
                    </div>

                    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
                    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const quill = new Quill('#editor', {
                                theme: 'snow',
                                placeholder: 'Write something...',
                            });

                            // Sync the content to the textarea
                            quill.on('text-change', function () {
                                document.querySelector('#pageDescription').value = quill.root.innerHTML;
                            });
                        });
                    </script>



                    <!-- Page Status -->
                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary" for="pageStatus">Status</label>
                        <select id="pageStatus" class="form-select" name="pageStatus" required>
                            <option value="active" {{ old('pageStatus', $page->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('pageStatus', $page->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if($errors->has('pageStatus'))
                            <div class="text-danger">{{ $errors->first('pageStatus') }}</div>
                        @endif
                    </div>


                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary" for="display">Display</label>
                        <select id="display" class="form-select select2" name="display" required>
                            <option value="approved" {{ old('display', $page->display) == 'approved' ? 'selected' : '' }}>Approved Users</option>
                            <option value="unapproved" {{ old('display', $page->display) == 'unapproved' ? 'selected' : '' }}>All Users</option>
                        </select>
                        @if($errors->has('display'))
                            <div class="text-danger">{{ $errors->first('display') }}</div>
                        @endif
                    </div>

                    <!-- Submit & Cancel Buttons -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 me-3">Update</button>
                        <a href="{{ route('pages.index') }}" class="btn btn-danger px-4">Cancel</a>
                    </div>
                </form>

                <!-- JavaScript for Slug Generation -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const pageNameInput = document.getElementById('pageName');
                        const pageSlugInput = document.getElementById('pageSlug');

                        // Event listener to generate slug dynamically
                        pageNameInput.addEventListener('keyup', function () {
                            const slug = pageNameInput.value
                                .toLowerCase()
                                .trim()
                                .replace(/[^a-z0-9\s-]/g, '')
                                .replace(/\s+/g, '-')
                                .replace(/-+/g, '-');
                            pageSlugInput.value = slug;
                        });
                    });
                </script>

            </div>
        </div>
    </div>
</div>

@endsection
