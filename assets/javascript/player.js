$(function () {
    $('[data-crtv-podcast="player"]').each(function () {
        var $container = $(this);
        var $embed = $container.find('[data-crtv-podcast-player]');
        var $releases = $container.find('.crtv-podcast-releases');

        $releases.find('[data-crtv-podcast-player-item]').on('click', function () {
            var $target = $(this);
            if ($target.parent().hasClass('active')) return false;

            var $embedEl;
            switch ($target.data('crtv-podcast-player-type')) {
                case 'audio':
                    $embedEl = $('<div>').addClass('crtv-embed-audio').css({
                        backgroundImage: "url('" + $container.data('crtv-podcast-player-poster') + "')"
                    }).append(
                        $('<audio>').attr({
                            controls: 'controls',
                            src: $target.data('crtv-podcast-player-item')
                        })
                    );
                    break;
                case 'video':
                    $embedEl = $('<video>').attr({
                        controls: 'controls',
                        poster: $container.data('crtv-podcast-player-poster'),
                        src: $target.data('crtv-podcast-player-item')
                    });
                    break;
                default:
                    $embedEl = $('<iframe>').attr({
                        frameborder: 0,
                        src: $target.data('crtv-podcast-player-item')
                    });
                    break;
            }
            $embed.empty().append($embedEl);
            $releases.find('.active').removeClass('active');
            $target.parent().addClass('active');

            return false;
        });
    });
});