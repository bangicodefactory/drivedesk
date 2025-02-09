<div class="e-signpad" data-disabled-without-signature="<?php echo e($disabledWithoutSignature); ?>" style="display: flex; flex-direction: column; align-items: center">
    <canvas style="touch-action: none; border: 1px solid <?php echo e($borderColor); ?>; max-width: 100%"
            width="<?php echo e($width); ?>"
            height="<?php echo e($height); ?>"
            class="<?php echo e($padClasses); ?>"></canvas>
    <div>
        <input type="hidden" name="sign" class="sign">
        <button type="button" class="sign-pad-button-clear <?php echo e($buttonClasses); ?>"><?php echo $clearName; ?></button>
        <button type="submit" class="sign-pad-button-submit <?php echo e($buttonClasses); ?>" <?php echo e($disabledWithoutSignature ? 'disabled' : ''); ?>><?php echo $submitName; ?></button>
    </div>
</div>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/vendor/creagia/laravel-sign-pad/src/../resources/views/components/signature-pad.blade.php ENDPATH**/ ?>