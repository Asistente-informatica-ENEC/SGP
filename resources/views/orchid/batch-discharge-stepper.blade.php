<div id="stepper-container-normal" style="display:none;">
    <div id="stepper-hidden-inputs-normal"></div>
    <div class="form-group">
        <label for="stepper-observation-normal" class="form-label">Observación general <sup class="text-danger">*</sup></label>
        <textarea id="stepper-observation-normal" class="form-control" rows="3" maxlength="500" placeholder="Motivo de descarga para todos los bienes seleccionados..."></textarea>
        <div class="invalid-feedback">La observación es obligatoria.</div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary" id="stepper-submit-normal">Confirmar Descarga</button>
    </div>
</div>

<div id="stepper-container-bad-condition" style="display:none;">
    <div id="stepper-hidden-inputs-bad-condition"></div>
    <div class="form-group">
        <label for="stepper-observation-bad-condition" class="form-label">Observación general <sup class="text-danger">*</sup></label>
        <textarea id="stepper-observation-bad-condition" class="form-control" rows="3" maxlength="500" placeholder="Motivo de descarga por mal estado para todos los bienes seleccionados..."></textarea>
        <div class="invalid-feedback">La observación es obligatoria.</div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-warning" id="stepper-submit-bad-condition">Confirmar Descarga</button>
    </div>
</div>
