$(document).ready(function() {
    $(document).on('click', 'a[data-toggle=\'image\']', function(e) {
        var $element = $(this);

        $('#button-clear').on('click', function() {
            var elementId = $element.parent().find('input').attr('id');

            if (elementId) {
                var changeEvent = document.createEvent("HTMLEvents");
                changeEvent.initEvent("change", false, true);
                var targetElement = document.getElementById(elementId);
                targetElement.dispatchEvent(changeEvent);
            }
        });
    });
});

