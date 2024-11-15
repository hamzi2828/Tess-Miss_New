<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
    <a
      class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
      href="javascript:void(0);"
      data-bs-toggle="dropdown"
      data-bs-auto-close="outside"
      aria-expanded="false">
      <span class="position-relative">
        <i class="ti ti-bell ti-md"></i>
        @if(auth()->user()->unreadNotifications->count() > 0)
          <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
        @endif
      </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end p-0">
      <li class="dropdown-menu-header border-bottom">
        <div class="dropdown-header d-flex align-items-center py-3">
          <h6 class="mb-0 me-auto">All Notifications</h6>
          <div class="d-flex align-items-center h6 mb-0">
            <span class="badge bg-label-primary me-2">
              {{ auth()->user()->unreadNotifications->count() }} New
            </span>
            {{-- <a
              href="javascript:void(0)"
              class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Mark all as read"
              onclick="event.preventDefault(); document.getElementById('mark-all-as-read').submit();"
            >
              <i class="ti ti-mail-opened text-heading"></i>
            </a> --}}
            <form id="mark-all-as-read" action="{{ route('notifications.markAllAsRead') }}" method="POST" style="display: none;">
              @csrf
            </form>
          </div>
        </div>
      </li>
      <li class="dropdown-notifications-list scrollable-container">
        <ul class="list-group list-group-flush" id="notifications-container">
            {{-- Initial Notifications (optional fallback if JavaScript is disabled) --}}
            @foreach(auth()->user()->notifications->where('type', '=', 'App\Notifications\MerchantActivityNotification')->take(10) as $notification)
            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <a
                    href="{{ isset($notification->data['activity_type']) && $notification->data['activity_type'] == 'store'
                    ? route('notifications.read', ['id' => $notification->id, 'merchant_id' => $notification->data['merchant_id']])
                    : route('edit.merchants.services', ['id' => $notification->id, 'merchant_id' => $notification->data['merchant_id']]) }}"
                    class="d-flex text-decoration-none">
                    <div class="flex-grow-1">
                        <h6 class="small mb-1">{{ $notification->data['message'] }}</h6>
                        @if(isset($notification->data['activity_type']) && $notification->data['activity_type'] == 'store')
                            <small class="text-muted">Added by: {{ $notification->data['added_by'] }}</small><br>
                        @endif
                        @if(isset($notification->data['activity_type']) && $notification->data['activity_type'] == 'approve')
                            <small class="text-muted">Approved by: {{ $notification->data['added_by'] }}</small><br>
                        @endif
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="flex-shrink-0 dropdown-notifications-actions">
                        @if(is_null($notification->read_at))
                            <span class="badge badge-dot bg-primary"></span>
                            <small>Unread</small>
                        @endif
                    </div>
                </a>
            </li>
            @endforeach
        </ul>
    </li>

      {{-- <li class="border-top">
        <div class="d-grid p-4">
          <a class="btn btn-primary btn-sm d-flex" href="{{ route('notifications.markAllAsRead') }}">
            <small class="align-middle">Read all notifications</small>
          </a>
        </div>
      </li> --}}
    </ul>
  </li>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const notificationsContainer = document.getElementById('notifications-container');
    const unreadCountBadge = document.querySelector('.badge-notifications.border');
    const unreadCountText = document.querySelector('.badge.bg-label-primary');

    async function fetchNotifications() {
        try {
            const response = await fetch('{{ route('notifications.latest') }}');
            const data = await response.json();

            // Update unread count
            if (data.unreadCount > 0) {
                unreadCountBadge.style.display = 'inline-block';
                unreadCountText.textContent = `${data.unreadCount} New`;
            } else {
                unreadCountBadge.style.display = 'none';
                unreadCountText.textContent = '0 New';
            }

            // Update notifications list
            notificationsContainer.innerHTML = '';
            if (data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    const listItem = `
                        <li class="list-group-item list-group-item-action dropdown-notifications-item">
                            <a
                                href="${notification.data.activity_type === 'store'
                                    ? `{{ route('notifications.read', ['id' => ':id', 'merchant_id' => ':merchant_id']) }}`
                                        .replace(':id', notification.id)
                                        .replace(':merchant_id', notification.data.merchant_id)
                                    : `{{ route('edit.merchants.services', ['id' => ':id', 'merchant_id' => ':merchant_id']) }}`
                                        .replace(':id', notification.id)
                                        .replace(':merchant_id', notification.data.merchant_id)}"
                                class="d-flex text-decoration-none">
                                <div class="flex-grow-1">
                                    <h6 class="small mb-1">${notification.data.message}</h6>
                                    ${notification.data.activity_type === 'store' ? `<small class="text-muted">Added by: ${notification.data.added_by}</small><br>` : ''}
                                    ${notification.data.activity_type === 'approve' ? `<small class="text-muted">Approved by: ${notification.data.added_by}</small><br>` : ''}
                                    <small class="text-muted">${new Date(notification.created_at).toLocaleString()}</small>
                                </div>
                                <div class="flex-shrink-0 dropdown-notifications-actions">
                                    ${notification.read_at === null ? `
                                        <span class="badge badge-dot bg-primary"></span>
                                        <small>Unread</small>` : ''}
                                </div>
                            </a>
                        </li>
                    `;
                    notificationsContainer.insertAdjacentHTML('beforeend', listItem);
                });
            } else {
                notificationsContainer.innerHTML = '<li class="text-center py-3 text-muted">No new notifications</li>';
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    // Fetch notifications every 10 seconds
    setInterval(fetchNotifications, 10000);

    // Initial fetch on page load
    fetchNotifications();
});

</script>
