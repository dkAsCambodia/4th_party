import './bootstrap';

 if (typeof toastr === 'undefined') {
    console.error('Toastr is not defined. Please ensure that the Toastr library is properly included.');
} else {
     toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: false,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: false,
        onclick: null,
        showDuration: '300',
        hideDuration: '1000',
        timeOut: '120000',  
        extendedTimeOut: '60000',  
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };

     console.log('Toastr options:', toastr.options);

     window.Echo.channel('my-channel')
        .listen('.form-submitted', (data) => {
             if (!data || !data.post) {
                console.error('Received data is undefined or does not contain expected post information:', data);
                return;
            }

             const audioElement = document.getElementById('notificationAudio');
            if (audioElement) {
                 audioElement.play().then(() => {
                    console.log('Notification sound played successfully.');
                }).catch(error => {
                    console.error('Audio playback failed:', error);
                    exit;
                });
            } else {
                console.error('Notification audio element not found.'); exit;
            }

             toastr.success(`Author: ${data.post.author}<br>Title: ${data.post.title}`, 'New Post Created');
        });
}