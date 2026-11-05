import test from 'node:test';
import assert from 'node:assert/strict';
import { constraintMessage } from '../../resources/js/app-dialogs.js';

test('form constraints explain recovery in Spanish regardless of browser language', () => {
    assert.equal(constraintMessage({ validity: { valueMissing: true }, type: 'email' }), 'Completa este campo para continuar.');
    assert.equal(constraintMessage({ validity: { valueMissing: true }, type: 'checkbox' }), 'Marca esta casilla para continuar.');
    assert.equal(constraintMessage({ validity: { typeMismatch: true }, type: 'email' }), 'Escribe un correo electrónico válido.');
    assert.equal(constraintMessage({ validity: { rangeUnderflow: true }, min: '0.01' }), 'El valor mínimo es 0.01.');
});

test('custom validation keeps the specific business instruction', () => {
    assert.equal(constraintMessage({ validity: { customError: true }, validationMessage: 'Las contraseñas no coinciden.' }), 'Las contraseñas no coinciden.');
});
