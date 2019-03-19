$(document).ready(function () {
    var repeater = $('.repeater-form').repeater({
        initEmpty: false,
        isFirstItemUndeletable: false
    })

    repeater.setList(valuesList)

    function copyElement(){
        var currentParent = $(this).parent();

        var utm,tag,replacer,def = '';
        utm = currentParent.find(':nth-child(1) input').val();
        tag = currentParent.find(':nth-child(2) input').val();
        replacer = currentParent.find(':nth-child(3) textarea').val();
        def = currentParent.find(':nth-child(4) textarea').val();

        valuesList.push({
            utm:utm,
            tag:tag,
            replacer:replacer,
            default:def
        });

        console.log(valuesList);

        repeater.setList(valuesList);

        setListeners();
    }



    function setListeners(){
        $('[data-repeater-delete]').click(function () {
            delete valuesList[$(this).parent().index()];
        });

        $('[data-repeater-create]').click(function () {
            $('body').off('click','[data-repeater-copy]',copyElement);
            $('[data-repeater-copy]').click(copyElement);
        });

        $('[data-repeater-copy]').click(copyElement);
    }

    setListeners();
});

