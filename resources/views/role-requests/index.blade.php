@extends('admin.layouts.master')

@section('contents')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.2/semantic.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.semanticui.css">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-xxl flex-grow-1 container-p-y bg-footer-theme">
    <h2 class="fw-bold">
        Role Requests
    </h2>
    <div class="card">
        <div class="card-body">
            <table id="role-requests-table" class="table table-bordered w-100">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>User</th>
                      <th>Email</th>
                      <th>Current Role</th>
                      <th>Requested Role</th>
                      <th>Reason</th>
                      <th>Requested At</th>
                      <th class="text-center">Action</th>
                  </tr>
              </thead>
          </table>
        </div>
    </div>
</div>
<x-modal id="roleRequestModal" title="Confirm Action" centered="true">
    <p id="roleRequestModalMessage"></p>
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmRoleRequestAction">Confirm</button>
    </div>
</x-modal>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.semanticui.js"></script>

<script>
$(document).ready(function () {
    const table = $('#role-requests-table').DataTable({
        processing: true,
        ajax: "{{ route('role-requests.data') }}",
        columns: [
          { data: 'id' },
          { data: 'user.name' },
          { data: 'user.email' },

          // CURRENT ROLE
          {
              data: 'user.roles',
              render: function(data) {
                  if (!data || data.length === 0) return '-';
                  return data.map(r => r.name).join(', ');
              }
          },

          // REQUESTED ROLE
          { data: 'role.name' },

          { data: 'reason', defaultContent: '-' },

          {
              data: 'created_at',
              render: function(date) {
                  const d = new Date(date);
                  return d.toLocaleString('en-US', {
                      month: 'short',
                      day: 'numeric',
                      year: 'numeric',
                      hour: 'numeric',
                      minute: '2-digit',
                      hour12: true
                  });
              }
          },

          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
                return `
                    <div style="display: flex; justify-content: center; gap: 5px;">
                        <button class="btn btn-success btn-sm"
                                onclick="showRoleRequestModal(${row.id}, 'approve', '${row.user.name}')">
                            Approve
                        </button>
                        <button class="btn btn-danger btn-sm"
                                onclick="showRoleRequestModal(${row.id}, 'reject', '${row.user.name}')">
                            Reject
                        </button>
                    </div>
                `;
            }
        }
      ]
    });

    // Bootstrap modal instance
    let roleRequestModal = new bootstrap.Modal(document.getElementById('roleRequestModal'));
    let currentActionId = null;
    let currentActionType = null;

    window.showRoleRequestModal = function(id, type, userName) {
        currentActionId = id;
        currentActionType = type;

        const message = type === 'approve'
            ? `Are you sure you want to approve the role request for ${userName}?`
            : `Are you sure you want to reject the role request for ${userName}?`;

        document.getElementById('roleRequestModalMessage').innerText = message;

        // Change confirm button color based on action
        const confirmBtn = document.getElementById('confirmRoleRequestAction');
        confirmBtn.classList.remove('btn-primary', 'btn-success', 'btn-danger');
        confirmBtn.classList.add(type === 'approve' ? 'btn-success' : 'btn-danger');

        roleRequestModal.show();
    };


    // Handle confirm button click
    document.getElementById('confirmRoleRequestAction').addEventListener('click', function() {
        if (!currentActionId || !currentActionType) return;

        // Determine URL based on action
        let url = '';
        if (currentActionType === 'approve') {
            url = "{{ route('role-requests.approve', ':id') }}".replace(':id', currentActionId);
        } else if (currentActionType === 'reject') {
            url = "{{ route('role-requests.reject', ':id') }}".replace(':id', currentActionId);
        }

        $.post(url, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(res) {
            if (res.success) {
              showToast(res.message, 'success');
            } else {
              showToast(res.message, 'error');
            }
            table.ajax.reload();
            roleRequestModal.hide();
        });
    });

    // Approve request
    window.approveRequest = function(id) {
        if(confirm('Are you sure you want to approve this request?')) {
            $.post("{{ url('role-requests') }}/" + id + "/approve", {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                alert(res.message || 'Request approved');
                table.ajax.reload();
            });
        }
    };

    // Reject request
    window.rejectRequest = function(id) {
        if(confirm('Are you sure you want to reject this request?')) {
            $.post("{{ url('role-requests') }}/" + id + "/reject", {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                alert(res.message || 'Request rejected');
                table.ajax.reload();
            });
        }
    };

});
</script>
@endpush
