<template>
<!-- MODAL DE ADVERTENCIA INICIAL -->
    <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden flex flex-col items-center">
            <img src="/recordatorio.png" alt="Recordatorio Importante" class="w-full h-auto object-cover" />
            <div class="p-4 w-full bg-gray-50 border-t">
                <button 
                    @click="cerrarModal" 
                    :disabled="contadorModal > 0"
                    :class="contadorModal > 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    class="w-full text-white font-bold py-3 px-4 rounded-md transition-colors"
                >
                    ACEPTAR {{ contadorModal > 0 ? `(${contadorModal})` : '' }}
                </button>
            </div>
        </div>
    </div>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">GENERADOR DE CARTAS DE INMATRICULACIÓN</h2>

                <form @submit.prevent="generarPdf">
                    
                    <!-- 1. SELECCIÓN DE CIUDAD (SUCURSALES) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Seleccione Ciudad de la Sucursal:</label>
                        <select v-model="form.ciudad" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <optgroup label="Autonort Trujillo">
                                <option value="Trujillo">Trujillo</option>
                                <option value="Huamachuco">Huamachuco</option>
                                <option value="Chimbote">Chimbote</option>
                                <option value="Barranca">Barranca</option>
                                <option value="Huaraz">Huaraz</option>
                            </optgroup>
                            <optgroup label="Autonort Cajamarca">
                                <option value="Cajamarca">Cajamarca</option>
                                <option value="Jaén">Jaén</option>
                                <option value="Talara">Talara</option>
                                <option value="Tumbes">Tumbes</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- NUEVO: SELECCIÓN DE STOCK -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Stock de la Unidad:</label>
                        <select v-model="form.stock" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="" disabled>Seleccione...</option>
                            <option value="TRUJILLO">Autonort Trujillo S.A.C.</option>
                            <option value="CAJAMARCA">Autonort Cajamarca S.A.C.</option>
                        </select>
                    </div>

                    <!-- 2. FECHA DEL DOCUMENTO -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Fecha del Documento:</label>
                        <input type="date" 
                            v-model="form.fecha" 
                            class="w-full border-gray-300 rounded-md shadow-sm bg-gray-200 text-gray-600 cursor-not-allowed pointer-events-none" 
                            readonly required />
                    </div>

                    <!-- 3. TIPO DE CLIENTE -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Tipo de Cliente:</label>
                        <select v-model="form.tipo_cliente" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="" disabled>Seleccione...</option>
                            <option value="Juridica">Persona Jurídica</option>
                            <option value="Natural">Persona Natural</option>
                            <option value="Copropiedad">Co Propiedad</option>
                        </select>
                    </div>

                    <hr class="my-6" />

                    <!-- ========================================== -->
                    <!-- APARTADO: DATOS DE CLIENTE (PERSONA JURÍDICA) -->
                    <!-- ========================================== -->
                    <div v-if="form.tipo_cliente === 'Juridica'">
                        <h3 class="text-xl font-semibold text-blue-600 mb-4">Datos de Persona Jurídica</h3>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700">RUC de la Empresa (11 dígitos):</label>
                                <input type="text" v-model="form.juridica.ruc" 
                                    @input="form.juridica.ruc = form.juridica.ruc.replace(/\D/g, '').slice(0, 11); form.juridica.tipo_doc_empresa = 'RUC'; buscarDocumento(form.juridica, 'ruc', 'tipo_doc_empresa', 'nombre_empresa')"
                                    minlength="11" maxlength="11" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                            <div>
                                <label class="block text-gray-700">Nombre de la Empresa:</label>
                                <input type="text" v-model="form.juridica.nombre_empresa" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700">Documento Representante Legal:</label>
                                <div class="flex gap-2">
                                    <select v-model="form.juridica.tipo_doc_representante" @change="limpiarDocumento(form.juridica, 'dni_representante', 'tipo_doc_representante')" class="w-1/3 border-gray-300 rounded-md shadow-sm" required>
                                        <option value="DNI">DNI</option>
                                        <option value="C.E.">C.E.</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                        <option value="RUC">RUC</option>
                                    </select>
                                    <input type="text" v-model="form.juridica.dni_representante" 
                                        @input="limpiarDocumento(form.juridica, 'dni_representante', 'tipo_doc_representante'); buscarDocumento(form.juridica, 'dni_representante', 'tipo_doc_representante', 'nombre_representante')" 
                                            :minlength="form.juridica.tipo_doc_representante === 'DNI' ? 8 : (form.juridica.tipo_doc_representante === 'RUC' ? 11 : null)" 
                                            :maxlength="form.juridica.tipo_doc_representante === 'DNI' ? 8 : (form.juridica.tipo_doc_representante === 'RUC' ? 11 : null)" 
                                            class="w-2/3 border-gray-300 rounded-md shadow-sm" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700">Nombre Representante Legal:</label>
                                <input type="text" v-model="form.juridica.nombre_representante" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700">N° de Partida Registral:</label>
                                <input type="text" v-model="form.juridica.partida" class="w-full border-gray-300 rounded-md shadow-sm" :required="form.juridica.provincia_registral.length > 0" />
                            </div>
                            <div>
                                <label class="block text-gray-700">Provincia (Oficina Registral):</label>
                                <div class="relative">
                                    <input type="text" v-model="form.juridica.provincia_registral" @focus="mostrarDropdownProvincia = true" @blur="validarProvincia" class="w-full border-gray-300 rounded-md shadow-sm pr-10 uppercase" placeholder="Seleccione..." autocomplete="off" :required="form.juridica.partida.length > 0" />
                                    <div class="absolute right-3 top-3 pointer-events-none text-gray-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                    <ul v-if="mostrarDropdownProvincia" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-48 overflow-y-auto rounded-md shadow-lg">
                                        <li v-for="prov in provinciasFiltradas" :key="prov" @mousedown.prevent="seleccionarProvincia(prov)" class="px-4 py-2 hover:bg-blue-600 hover:text-white cursor-pointer uppercase">{{ prov }}</li>
                                        <li v-if="provinciasFiltradas.length === 0" class="px-4 py-2 text-gray-500">No se encontraron resultados</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- APARTADO: DATOS DE CLIENTE (PERSONA NATURAL) -->
                    <!-- ========================================== -->
                    <div v-if="form.tipo_cliente === 'Natural'">
                        <h3 class="text-xl font-semibold text-blue-600 mb-4">Datos de Persona Natural</h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700">Documento de Identidad:</label>
                                <div class="flex gap-2">
                                    <select v-model="form.natural.tipo_doc" @change="limpiarDocumento(form.natural, 'dni', 'tipo_doc')" class="w-1/3 border-gray-300 rounded-md shadow-sm" required>
                                        <option value="DNI">DNI</option>
                                        <option value="C.E.">C.E.</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                        <option value="RUC">RUC</option>
                                    </select>
                                    <input type="text" v-model="form.natural.dni"
                                        @input="limpiarDocumento(form.natural, 'dni', 'tipo_doc'); buscarDocumento(form.natural, 'dni', 'tipo_doc', 'nombre')"
                                            :minlength="form.natural.tipo_doc === 'DNI' ? 8 : (form.natural.tipo_doc === 'RUC' ? 11 : null)"
                                            :maxlength="form.natural.tipo_doc === 'DNI' ? 8 : (form.natural.tipo_doc === 'RUC' ? 11 : null)"
                                            class="w-full border-gray-300 rounded-md shadow-sm" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700">Nombre Completo:</label>
                                <input type="text" v-model="form.natural.nombre" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700">Estado Civil:</label>
                            <select v-model="form.natural.estado_civil" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="SOLTERO">Soltero/a</option>
                                <option value="CASADO">Casado/a</option>
                                <option value="VIUDO">Viudo/a</option>
                                <option value="DIVORCIADO">Divorciado/a</option>
                            </select>
                        </div>

                        <!-- BLOQUE: UNIÓN DE HECHO (SOLO PARA SOLTEROS) -->
                        <div v-if="form.natural.estado_civil === 'SOLTERO'" class="mb-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <label class="block text-gray-700 font-bold mb-2">¿Tiene Unión de Hecho registrada?</label>
                            <select v-model="form.natural.union_hecho" class="w-full border-gray-300 rounded-md shadow-sm mb-4">
                                <option value="NO">NO</option>
                                <option value="SI">SI</option>
                            </select>

                            <div v-if="form.natural.union_hecho === 'SI'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700">DNI de la Pareja:</label>
                                    <input type="text" v-model="form.natural.dni_union" 
                                        @input="form.natural.dni_union = form.natural.dni_union.replace(/\D/g, '').slice(0, 8); form.natural.tipo_doc_union = 'DNI'; buscarDocumento(form.natural, 'dni_union', 'tipo_doc_union', 'nombre_union')"
                                        minlength="8" maxlength="8" class="w-full border-gray-300 rounded-md shadow-sm" required />
                                </div>
                                <div>
                                    <label class="block text-gray-700">Nombre de la Pareja:</label>
                                    <input type="text" v-model="form.natural.nombre_union" class="w-full border-gray-300 rounded-md shadow-sm" required />
                                </div>
                                <div>
                                    <label class="block text-gray-700">N° de Partida Registral:</label>
                                    <input type="text" v-model="form.natural.partida_union" class="w-full border-gray-300 rounded-md shadow-sm" required />
                                </div>
                                <div>
                                    <label class="block text-gray-700">Sede (Oficina Registral):</label>
                                    <select v-model="form.natural.sede_union" class="w-full border-gray-300 rounded-md shadow-sm uppercase" required>
                                        <option value="" disabled>Seleccione...</option>
                                        <option v-for="prov in listaProvincias" :key="prov" :value="prov">{{ prov }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- BLOQUE CASADOS: SEPARACIÓN DE BIENES O CÓNYUGE -->
                        <div v-if="form.natural.estado_civil === 'CASADO'" class="mb-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <label class="block text-gray-700 font-bold mb-2">¿Tienen Separación de Bienes?</label>
                            <select v-model="form.natural.bienes_separados" class="w-full border-gray-300 rounded-md shadow-sm mb-4">
                                <option value="NO">NO</option>
                                <option value="SI">SI</option>
                            </select>

                            <div v-if="form.natural.bienes_separados === 'NO'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700">Documento del Cónyuge:</label>
                                    <div class="flex gap-2">
                                        <select v-model="form.natural.tipo_doc_conyuge" @change="limpiarDocumento(form.natural, 'dni_conyuge', 'tipo_doc_conyuge')" class="w-1/3 border-gray-300 rounded-md shadow-sm" :required="form.natural.bienes_separados === 'NO'">
                                            <option value="DNI">DNI</option>
                                            <option value="C.E.">C.E.</option>
                                            <option value="PASAPORTE">Pasaporte</option>
                                            <option value="RUC">RUC</option>
                                        </select>
                                        <input type="text" v-model="form.natural.dni_conyuge" 
                                            @input="limpiarDocumento(form.natural, 'dni_conyuge', 'tipo_doc_conyuge'); buscarDocumento(form.natural, 'dni_conyuge', 'tipo_doc_conyuge', 'nombre_conyuge')"
                                            :minlength="form.natural.tipo_doc_conyuge === 'DNI' ? 8 : (form.natural.tipo_doc_conyuge === 'RUC' ? 11 : null)" 
                                            :maxlength="form.natural.tipo_doc_conyuge === 'DNI' ? 8 : (form.natural.tipo_doc_conyuge === 'RUC' ? 11 : null)" 
                                            class="w-2/3 border-gray-300 rounded-md shadow-sm" :required="form.natural.bienes_separados === 'NO'" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700">Nombre Completo del Cónyuge:</label>
                                    <input type="text" v-model="form.natural.nombre_conyuge" class="w-full border-gray-300 rounded-md shadow-sm" :required="form.natural.bienes_separados === 'NO'" />
                                </div>
                            </div>

                            <div v-if="form.natural.bienes_separados === 'SI'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700">N° de Partida Registral:</label>
                                    <input type="text" v-model="form.natural.partida_bienes" class="w-full border-gray-300 rounded-md shadow-sm" :required="form.natural.bienes_separados === 'SI'" />
                                </div>
                                <div>
                                    <label class="block text-gray-700">Sede (Oficina Registral):</label>
                                    <select v-model="form.natural.sede_bienes" class="w-full border-gray-300 rounded-md shadow-sm uppercase" :required="form.natural.bienes_separados === 'SI'">
                                        <option value="" disabled>Seleccione...</option>
                                        <option v-for="prov in listaProvincias" :key="prov" :value="prov">{{ prov }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- NUEVO: SELECTORES DINÁMICOS DE UBIGEO -->
                        <div class="mb-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <label class="block text-gray-700 font-bold mb-4">Seleccione Ubigeo del Domicilio:</label>
                            
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 text-sm">Departamento:</label>
                                    <select v-model="depNatural" @change="cambioDepartamento" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        <option v-for="dep in departamentos" :key="dep" :value="dep">{{ dep }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm">Provincia:</label>
                                    <select v-model="provNatural" @change="cambioProvincia" :disabled="!depNatural" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        <option v-for="prov in provincias" :key="prov" :value="prov">{{ prov }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm">Distrito:</label>
                                    <select v-model="distNatural" @change="cambioDistrito" :disabled="!provNatural" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        <option v-for="dist in distritos" :key="dist" :value="dist">{{ dist }}</option>
                                    </select>
                                </div>
                            </div>

                            <label class="block text-gray-700 font-bold">Domicilio: DIRECCIÓN + UBIGEO(DEPARTAMENTO - PROVINCIA - DISTRITO)</label>
                            <input type="text" v-model="form.natural.domicilio" placeholder="Ej: Av. Larco 123 - LA LIBERTAD - TRUJILLO - TRUJILLO" class="w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- APARTADO: CO PROPIEDAD                     -->
                    <!-- ========================================== -->
                    <div v-if="form.tipo_cliente === 'Copropiedad'">
                        <h3 class="text-xl font-semibold text-blue-600 mb-4">Datos de Co-Propietarios</h3>
                        
                        <div v-for="(propietario, index) in form.copropiedad.lista" :key="index" class="border p-4 rounded-md mb-4 bg-gray-50 relative">
                            
                            <button v-if="index > 0" @click.prevent="eliminarCopropietario(index)" type="button" class="absolute top-4 right-4 text-red-500 hover:text-red-700 font-bold text-sm">
                                X Eliminar
                            </button>

                            <h4 class="font-bold text-gray-700 mb-2">Propietario {{ index + 1 }}</h4>
                            
                            <div class="grid grid-cols-2 gap-4 mb-2">
                                <div>
                                    <div class="flex gap-2">
                                        <select v-model="propietario.tipo_doc" @change="limpiarDocumento(propietario, 'dni', 'tipo_doc')" class="w-1/3 border-gray-300 rounded-md shadow-sm" required>
                                            <option value="DNI">DNI</option>
                                            <option value="C.E.">C.E.</option>
                                            <option value="PASAPORTE">Pasaporte</option>
                                            <option value="RUC">RUC</option>
                                        </select>
                                        <input type="text" v-model="propietario.dni" 
                                            @input="limpiarDocumento(propietario, 'dni', 'tipo_doc'); buscarDocumento(propietario, 'dni', 'tipo_doc', 'nombre')"
                                                :minlength="propietario.tipo_doc === 'DNI' ? 8 : (propietario.tipo_doc === 'RUC' ? 11 : null)" 
                                                :maxlength="propietario.tipo_doc === 'DNI' ? 8 : (propietario.tipo_doc === 'RUC' ? 11 : null)" 
                                                placeholder="N° Documento" class="w-2/3 border-gray-300 rounded-md shadow-sm" required />
                                    </div>
                                </div>
                                <div>
                                    <input type="text" v-model="propietario.nombre" placeholder="Nombre Completo" class="w-full border-gray-300 rounded-md shadow-sm" required />
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <select v-model="propietario.estado_civil" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="SOLTERO">Soltero/a</option>
                                    <option value="CASADO">Casado/a</option>
                                    <option value="VIUDO">Viudo/a</option>
                                    <option value="DIVORCIADO">Divorciado/a</option>
                                </select>
                            </div>
                            
                            <div v-if="propietario.estado_civil === 'CASADO'" class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <div class="flex gap-2">
                                        <select v-model="propietario.tipo_doc_conyuge" @change="limpiarDocumento(propietario, 'dni_conyuge', 'tipo_doc_conyuge')" class="w-1/3 border-gray-300 rounded-md shadow-sm" required>
                                            <option value="DNI">DNI</option>
                                            <option value="C.E.">C.E.</option>
                                            <option value="PASAPORTE">Pasaporte</option>
                                            <option value="RUC">RUC</option>
                                        </select>
                                        <input type="text" v-model="propietario.dni_conyuge" 
                                            @input="limpiarDocumento(propietario, 'dni_conyuge', 'tipo_doc_conyuge'); buscarDocumento(propietario, 'dni_conyuge', 'tipo_doc_conyuge', 'nombre_conyuge')"
                                                :minlength="propietario.tipo_doc_conyuge === 'DNI' ? 8 : (propietario.tipo_doc_conyuge === 'RUC' ? 11 : null)" 
                                                :maxlength="propietario.tipo_doc_conyuge === 'DNI' ? 8 : (propietario.tipo_doc_conyuge === 'RUC' ? 11 : null)" 
                                                placeholder="N° Documento Cónyuge" class="w-2/3 border-gray-300 rounded-md shadow-sm" required />
                                    </div>
                                </div>
                                <div>
                                    <input type="text" v-model="propietario.nombre_conyuge" placeholder="Nombre Completo Cónyuge" class="w-full border-gray-300 rounded-md shadow-sm" required />
                                </div>
                            </div>
                        </div>

                        <button @click.prevent="agregarCopropietario" type="button" class="mb-6 bg-green-500 text-white font-bold py-2 px-4 rounded-md hover:bg-green-600">
                            + Agregar Co-Propietario
                        </button>

                        <!-- NUEVO: SELECTORES DINÁMICOS DE UBIGEO PARA CO-PROPIEDAD -->
                        <div class="mb-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <label class="block text-gray-700 font-bold mb-4">Seleccione Ubigeo del Domicilio Principal:</label>
                            
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 text-sm">Departamento:</label>
                                    <select v-model="depCopropiedad" @change="cambioDepartamentoCopropiedad" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        <option v-for="dep in departamentos" :key="dep" :value="dep">{{ dep }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm">Provincia:</label>
                                    <select v-model="provCopropiedad" @change="cambioProvinciaCopropiedad" :disabled="!depCopropiedad" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        <option v-for="prov in provinciasCopropiedad" :key="prov" :value="prov">{{ prov }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm">Distrito:</label>
                                    <select v-model="distCopropiedad" @change="cambioDistritoCopropiedad" :disabled="!provCopropiedad" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        <option v-for="dist in distritosCopropiedad" :key="dist" :value="dist">{{ dist }}</option>
                                    </select>
                                </div>
                            </div>

                            <label class="block text-gray-700 font-bold">Domicilio Principal: DIRECCIÓN + UBIGEO(DEPARTAMENTO - PROVINCIA - DISTRITO)</label>
                            <input type="text" v-model="form.copropiedad.domicilio" placeholder="Ej: Av. Larco 123 - LA LIBERTAD - TRUJILLO - TRUJILLO" class="w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>
                    </div>

                    <hr class="my-6" />

                    <!-- ========================================== -->
                    <!-- APARTADO: DATOS DEL VEHÍCULO               -->
                    <!-- ========================================== -->
                    <h3 class="text-xl font-semibold text-blue-600 mb-4">Datos del Vehículo</h3>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700">Marca:</label>
                            <select v-model="form.vehiculo.marca" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="TOYOTA">TOYOTA</option>
                                <option value="HINO">HINO</option>
                            </select>
                        </div>
                        
                        <div class="relative">
                            <label class="block text-gray-700">Modelo:</label>
                            <input type="text" 
                                v-model="form.vehiculo.modelo" 
                                @focus="mostrarDropdown = true"
                                @blur="validarModelo"
                                class="w-full border-gray-300 rounded-md shadow-sm pr-10 uppercase" 
                                placeholder="Buscar o seleccionar..." required autocomplete="off" />
                            <div class="absolute right-3 top-9 pointer-events-none text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                            <ul v-if="mostrarDropdown" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-48 overflow-y-auto rounded-md shadow-lg uppercase">
                                <li v-for="modelo in modelosFiltrados" :key="modelo"
                                    @mousedown.prevent="seleccionarModelo(modelo)"
                                    class="px-4 py-2 hover:bg-blue-600 hover:text-white cursor-pointer">
                                    {{ modelo }}
                                </li>
                                <li v-if="modelosFiltrados.length === 0" class="px-4 py-2 text-gray-500">
                                    No se encontraron resultados
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-700">N° de  Chasis:</label>
                            <input type="text" 
                                v-model="form.vehiculo.serie_chasis" 
                                maxlength="19"
                                @input="form.vehiculo.serie_chasis = form.vehiculo.serie_chasis.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()"
                                class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-gray-700">N° de Motor:</label>
                            <input type="text" 
                                v-model="form.vehiculo.motor" 
                                maxlength="12"
                                @input="form.vehiculo.motor = form.vehiculo.motor.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()"
                                class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-md hover:bg-blue-700">
                        Generar PDFs
                    </button>

                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';

const fechaHoy = new Date().toISOString().split('T')[0];

const mostrarModal = ref(true);
const contadorModal = ref(5);

// ==========================================
// DATA GIGANTE DE UBIGEOS INYECTADA DIRECTAMENTE
// ==========================================
const ubigeosData = {
    "AMAZONAS": {
        "BAGUA": {
            "ARAMANGO": {}, "BAGUA": {}, "COPALLIN": {}, "EL PARCO": {}, "IMAZA": {}, "LA PECA": {}
        },
        "BONGARA": {
            "CHISQUILLA": {}, "CHURUJA": {}, "COROSHA": {}, "CUISPES": {}, "FLORIDA": {}, "JAZAN": {}, "JUMBILLA": {}, "RECTA": {}, "SAN CARLOS": {}, "SHIPASBAMBA": {}, "VALERA": {}, "YAMBRASBAMBA": {}
        },
        "CHACHAPOYAS": {
            "ASUNCION": {}, "BALSAS": {}, "CHACHAPOYAS": {}, "CHETO": {}, "CHILIQUIN": {}, "CHUQUIBAMBA": {}, "GRANADA": {}, "HUANCAS": {}, "LA JALCA": {}, "LEIMEBAMBA": {}, "LEVANTO": {}, "MAGDALENA": {}, "MARISCAL CASTILLA": {}, "MOLINOPAMPA": {}, "MONTEVIDEO": {}, "OLLEROS": {}, "QUINJALCA": {}, "SAN FRANCISCO DE DAGUAS": {}, "SAN ISIDRO DE MAINO": {}, "SOLOCO": {}, "SONCHE": {}
        },
        "CONDORCANQUI": {
            "EL CENEPA": {}, "NIEVA": {}, "RIO SANTIAGO": {}
        },
        "LUYA": {
            "CAMPORREDONDO": {}, "COCABAMBA": {}, "COLCAMAR": {}, "CONILA": {}, "INGUILPATA": {}, "LAMUD": {}, "LONGUITA": {}, "LONYA CHICO": {}, "LUYA": {}, "LUYA VIEJO": {}, "MARIA": {}, "OCALLI": {}, "OCUMAL": {}, "PISUQUIA": {}, "PROVIDENCIA": {}, "SAN CRISTOBAL": {}, "SAN FRANCISCO DEL YESO": {}, "SAN JERONIMO": {}, "SAN JUAN DE LOPECANCHA": {}, "SANTA CATALINA": {}, "SANTO TOMAS": {}, "TINGO": {}, "TRITA": {}
        },
        "RODRIGUEZ DE MENDOZA": {
            "CHIRIMOTO": {}, "COCHAMAL": {}, "HUAMBO": {}, "LIMABAMBA": {}, "LONGAR": {}, "MARISCAL BENAVIDES": {}, "MILPUC": {}, "OMIA": {}, "SAN NICOLAS": {}, "SANTA ROSA": {}, "TOTORA": {}, "VISTA ALEGRE": {}
        },
        "UTCUBAMBA": {
            "BAGUA GRANDE": {}, "CAJARURO": {}, "CUMBA": {}, "EL MILAGRO": {}, "JAMALCA": {}, "LONYA GRANDE": {}, "YAMON": {}
        }
    },
    "ANCASH": {
        "AIJA": {
            "AIJA": {}, "CORIS": {}, "HUACLLAN": {}, "LA MERCED": {}, "SUCCHA": {}
        },
        "ANTONIO RAYMONDI": {
            "ACZO": {}, "CHACCHO": {}, "CHINGAS": {}, "LLAMELLIN": {}, "MIRGAS": {}, "SAN JUAN DE RONTOY": {}
        },
        "ASUNCION": {
            "ACOCHACA": {}, "CHACAS": {}
        },
        "BOLOGNESI": {
            "ABELARDO PARDO LEZAMETA": {}, "ANTONIO RAYMONDI": {}, "AQUIA": {}, "CAJACAY": {}, "CANIS": {}, "CHIQUIAN": {}, "COLQUIOC": {}, "HUALLANCA": {}, "HUASTA": {}, "HUAYLLACAYAN": {}, "LA PRIMAVERA": {}, "MANGAS": {}, "PACLLON": {}, "SAN MIGUEL DE CORPANQUI": {}, "TICLLOS": {}
        },
        "CARHUAZ": {
            "ACOPAMPA": {}, "AMASHCA": {}, "ANTA": {}, "ATAQUERO": {}, "CARHUAZ": {}, "MARCARA": {}, "PARIAHUANCA": {}, "SAN MIGUEL DE ACO": {}, "SHILLA": {}, "TINCO": {}, "YUNGAR": {}
        },
        "CARLOS FERMIN FITZCARRALD": {
            "SAN LUIS": {}, "SAN NICOLAS": {}, "YAUYA": {}
        },
        "CASMA": {
            "BUENA VISTA ALTA": {}, "CASMA": {}, "COMANDANTE NOEL": {}, "YAUTAN": {}
        },
        "CORONGO": {
            "ACO": {}, "BAMBAS": {}, "CORONGO": {}, "CUSCA": {}, "LA PAMPA": {}, "YANAC": {}, "YUPAN": {}
        },
        "HUARAZ": {
            "COCHABAMBA": {}, "COLCABAMBA": {}, "HUANCHAY": {}, "HUARAZ": {}, "INDEPENDENCIA": {}, "JANGAS": {}, "LA LIBERTAD": {}, "OLLEROS": {}, "PAMPAS": {}, "PARIACOTO": {}, "PIRA": {}, "TARICA": {}
        },
        "HUARI": {
            "ANRA": {}, "CAJAY": {}, "CHAVIN DE HUANTAR": {}, "HUACACHI": {}, "HUACCHIS": {}, "HUACHIS": {}, "HUANTAR": {}, "HUARI": {}, "MASIN": {}, "PAUCAS": {}, "PONTO": {}, "RAHUAPAMPA": {}, "RAPAYAN": {}, "SAN MARCOS": {}, "SAN PEDRO DE CHANA": {}, "UCO": {}
        },
        "HUARMEY": {
            "COCHAPETI": {}, "CULEBRAS": {}, "HUARMEY": {}, "HUAYAN": {}, "MALVAS": {}
        },
        "HUAYLAS": {
            "CARAZ": {}, "HUALLANCA": {}, "HUATA": {}, "HUAYLAS": {}, "MATO": {}, "PAMPAROMAS": {}, "PUEBLO LIBRE": {}, "SANTA CRUZ": {}, "SANTO TORIBIO": {}, "YURACMARCA": {}
        },
        "MARISCAL LUZURIAGA": {
            "CASCA": {}, "ELEAZAR GUZMAN BARRON": {}, "FIDEL OLIVAS ESCUDERO": {}, "LLAMA": {}, "LLUMPA": {}, "LUCMA": {}, "MUSGA": {}, "PISCOBAMBA": {}
        },
        "OCROS": {
            "ACAS": {}, "CAJAMARQUILLA": {}, "CARHUAPAMPA": {}, "COCHAS": {}, "CONGAS": {}, "LLIPA": {}, "OCROS": {}, "SAN CRISTOBAL DE RAJAN": {}, "SAN PEDRO": {}, "SANTIAGO DE CHILCAS": {}
        },
        "PALLASCA": {
            "BOLOGNESI": {}, "CABANA": {}, "CONCHUCOS": {}, "HUACASCHUQUE": {}, "HUANDOVAL": {}, "LACABAMBA": {}, "LLAPO": {}, "PALLASCA": {}, "PAMPAS": {}, "SANTA ROSA": {}, "TAUCA": {}
        },
        "POMABAMBA": {
            "HUAYLLAN": {}, "PAROBAMBA": {}, "POMABAMBA": {}, "QUINUABAMBA": {}
        },
        "RECUAY": {
            "CATAC": {}, "COTAPARACO": {}, "HUAYLLAPAMPA": {}, "LLACLLIN": {}, "MARCA": {}, "PAMPAS CHICO": {}, "PARARIN": {}, "RECUAY": {}, "TAPACOCHA": {}, "TICAPAMPA": {}
        },
        "SANTA": {
            "CACERES DEL PERU": {}, "CHIMBOTE": {}, "COISHCO": {}, "MACATE": {}, "MORO": {}, "NEPEÑA": {}, "NUEVO CHIMBOTE": {}, "SAMANCO": {}, "SANTA": {}
        },
        "SIHUAS": {
            "ACOBAMBA": {}, "ALFONSO UGARTE": {}, "CASHAPAMPA": {}, "CHINGALPO": {}, "HUAYLLABAMBA": {}, "QUICHES": {}, "RAGASH": {}, "SAN JUAN": {}, "SICSIBAMBA": {}, "SIHUAS": {}
        },
        "YUNGAY": {
            "CASCAPARA": {}, "MANCOS": {}, "MATACOTO": {}, "QUILLO": {}, "RANRAHIRCA": {}, "SHUPLUY": {}, "YANAMA": {}, "YUNGAY": {}
        }
    },
    "APURIMAC": {
        "ABANCAY": {
            "ABANCAY": {}, "CHACOCHE": {}, "CIRCA": {}, "CURAHUASI": {}, "HUANIPACA": {}, "LAMBRAMA": {}, "PICHIRHUA": {}, "SAN PEDRO DE CACHORA": {}, "TAMBURCO": {}
        },
        "ANDAHUAYLAS": {
            "ANDAHUAYLAS": {}, "ANDARAPA": {}, "CHIARA": {}, "HUANCARAMA": {}, "HUANCARAY": {}, "HUAYANA": {}, "JOSE MARIA ARGUEDAS": {}, "KAQUIABAMBA": {}, "KISHUARA": {}, "PACOBAMBA": {}, "PACUCHA": {}, "PAMPACHIRI": {}, "POMACOCHA": {}, "SAN ANTONIO DE CACHI": {}, "SAN JERONIMO": {}, "SAN MIGUEL DE CHACCRAMPA": {}, "SANTA MARIA DE CHICMO": {}, "TALAVERA": {}, "TUMAY HUARACA": {}, "TURPO": {}
        },
        "ANTABAMBA": {
            "ANTABAMBA": {}, "EL ORO": {}, "HUAQUIRCA": {}, "JUAN ESPINOZA MEDRANO": {}, "OROPESA": {}, "PACHACONAS": {}, "SABAINO": {}
        },
        "AYMARAES": {
            "CAPAYA": {}, "CARAYBAMBA": {}, "CHALHUANCA": {}, "CHAPIMARCA": {}, "COLCABAMBA": {}, "COTARUSE": {}, "HUAYLLO": {}, "JUSTO APU SAHUARAURA": {}, "LUCRE": {}, "POCOHUANCA": {}, "SAN JUAN DE CHACÑA": {}, "SAÑAYCA": {}, "SORAYA": {}, "TAPAIRIHUA": {}, "TINTAY": {}, "TORAYA": {}, "YANACA": {}
        },
        "CHINCHEROS": {
            "AHUAYRO": {}, "ANCO-HUALLO": {}, "CHINCHEROS": {}, "COCHARCAS": {}, "EL PORVENIR": {}, "HUACCANA": {}, "LOS CHANKAS": {}, "OCOBAMBA": {}, "ONGOY": {}, "RANRACANCHA": {}, "ROCCHACC": {}, "URANMARCA": {}
        },
        "COTABAMBAS": {
            "CHALLHUAHUACHO": {}, "COTABAMBAS": {}, "COYLLURQUI": {}, "HAQUIRA": {}, "MARA": {}, "TAMBOBAMBA": {}
        },
        "GRAU": {
            "CHUQUIBAMBILLA": {}, "CURASCO": {}, "CURPAHUASI": {}, "GAMARRA": {}, "HUAYLLATI": {}, "MAMARA": {}, "MICAELA BASTIDAS": {}, "PATAYPAMPA": {}, "PROGRESO": {}, "SAN ANTONIO": {}, "SANTA ROSA": {}, "TURPAY": {}, "VILCABAMBA": {}, "VIRUNDO": {}
        }
    },
    "AREQUIPA": {
        "AREQUIPA": {
            "ALTO SELVA ALEGRE": {}, "AREQUIPA": {}, "CAYMA": {}, "CERRO COLORADO": {}, "CHARACATO": {}, "CHIGUATA": {}, "JACOBO HUNTER": {}, "JOSE LUIS BUSTAMANTE Y RIVERO": {}, "LA JOYA": {}, "MARIANO MELGAR": {}, "MIRAFLORES": {}, "MOLLEBAYA": {}, "PAUCARPATA": {}, "POCSI": {}, "POLOBAYA": {}, "QUEQUEÑA": {}, "SABANDIA": {}, "SACHACA": {}, "SAN JUAN DE SIGUAS": {}, "SAN JUAN DE TARUCANI": {}, "SANTA ISABEL DE SIGUAS": {}, "SANTA RITA DE SIGUAS": {}, "SOCABAYA": {}, "TIABAYA": {}, "UCHUMAYO": {}, "VITOR": {}, "YANAHUARA": {}, "YARABAMBA": {}, "YURA": {}
        },
        "CAMANA": {
            "CAMANA": {}, "JOSE MARIA QUIMPER": {}, "MARIANO NICOLAS VALCARCEL": {}, "MARISCAL CACERES": {}, "NICOLAS DE PIEROLA": {}, "OCOÑA": {}, "QUILCA": {}, "SAMUEL PASTOR": {}
        },
        "CARAVELI": {
            "ACARI": {}, "ATICO": {}, "ATIQUIPA": {}, "BELLA UNION": {}, "CAHUACHO": {}, "CARAVELI": {}, "CHALA": {}, "CHAPARRA": {}, "HUANUHUANU": {}, "JAQUI": {}, "LOMAS": {}, "QUICACHA": {}, "YAUCA": {}
        },
        "CASTILLA": {
            "ANDAGUA": {}, "APLAO": {}, "AYO": {}, "CHACHAS": {}, "CHILCAYMARCA": {}, "CHOCO": {}, "HUANCARQUI": {}, "MACHAGUAY": {}, "ORCOPAMPA": {}, "PAMPACOLCA": {}, "TIPAN": {}, "UÑON": {}, "URACA": {}, "VIRACO": {}
        },
        "CAYLLOMA": {
            "ACHOMA": {}, "CABANACONDE": {}, "CALLALLI": {}, "CAYLLOMA": {}, "CHIVAY": {}, "COPORAQUE": {}, "HUAMBO": {}, "HUANCA": {}, "ICHUPAMPA": {}, "LARI": {}, "LLUTA": {}, "MACA": {}, "MADRIGAL": {}, "MAJES": {}, "SAN ANTONIO DE CHUCA": {}, "SIBAYO": {}, "TAPAY": {}, "TISCO": {}, "TUTI": {}, "YANQUE": {}
        },
        "CONDESUYOS": {
            "ANDARAY": {}, "CAYARANI": {}, "CHICHAS": {}, "CHUQUIBAMBA": {}, "IRAY": {}, "RIO GRANDE": {}, "SALAMANCA": {}, "YANAQUIHUA": {}
        },
        "ISLAY": {
            "COCACHACRA": {}, "DEAN VALDIVIA": {}, "ISLAY": {}, "MEJIA": {}, "MOLLENDO": {}, "PUNTA DE BOMBON": {}
        },
        "LA UNION": {
            "ALCA": {}, "CHARCANA": {}, "COTAHUASI": {}, "HUAYNACOTAS": {}, "PAMPAMARCA": {}, "PUYCA": {}, "QUECHUALLA": {}, "SAYLA": {}, "TAURIA": {}, "TOMEPAMPA": {}, "TORO": {}
        }
    },
    "AYACUCHO": {
        "CANGALLO": {
            "CANGALLO": {}, "CHUSCHI": {}, "LOS MOROCHUCOS": {}, "MARIA PARADO DE BELLIDO": {}, "PARAS": {}, "TOTOS": {}
        },
        "HUAMANGA": {
            "ACOCRO": {}, "ACOS VINCHOS": {}, "ANDRES AVELINO CACERES DORREGARAY": {}, "AYACUCHO": {}, "CARMEN ALTO": {}, "CHIARA": {}, "JESUS NAZARENO": {}, "OCROS": {}, "PACAYCASA": {}, "QUINUA": {}, "SAN JOSE DE TICLLAS": {}, "SAN JUAN BAUTISTA": {}, "SANTIAGO DE PISCHA": {}, "SOCOS": {}, "TAMBILLO": {}, "VINCHOS": {}
        },
        "HUANCA SANCOS": {
            "CARAPO": {}, "SACSAMARCA": {}, "SANCOS": {}, "SANTIAGO DE LUCANAMARCA": {}
        },
        "HUANTA": {
            "AYAHUANCO": {}, "CANAYRE": {}, "CHACA": {}, "HUAMANGUILLA": {}, "HUANTA": {}, "IGUAIN": {}, "LLOCHEGUA": {}, "LURICOCHA": {}, "PUCACOLPA": {}, "PUTIS": {}, "SANTILLANA": {}, "SIVIA": {}, "UCHURACCAY": {}
        },
        "LA MAR": {
            "ANCHIHUAY": {}, "ANCO": {}, "AYNA": {}, "CHILCAS": {}, "CHUNGUI": {}, "LUIS CARRANZA": {}, "NINABAMBA": {}, "ORONCCOY": {}, "PATIBAMBA": {}, "RIO MAGDALENA": {}, "SAMUGARI": {}, "SAN MIGUEL": {}, "SANTA ROSA": {}, "TAMBO": {}, "UNION PROGRESO": {}
        },
        "LUCANAS": {
            "AUCARA": {}, "CABANA": {}, "CARMEN SALCEDO": {}, "CHAVIÑA": {}, "CHIPAO": {}, "HUAC-HUAS": {}, "LARAMATE": {}, "LEONCIO PRADO": {}, "LLAUTA": {}, "LUCANAS": {}, "OCAÑA": {}, "OTOCA": {}, "PUQUIO": {}, "SAISA": {}, "SAN CRISTOBAL": {}, "SAN JUAN": {}, "SAN PEDRO": {}, "SAN PEDRO DE PALCO": {}, "SANCOS": {}, "SANTA ANA DE HUAYCAHUACHO": {}, "SANTA LUCIA": {}
        },
        "PARINACOCHAS": {
            "CHUMPI": {}, "CORACORA": {}, "CORONEL CASTAÑEDA": {}, "PACAPAUSA": {}, "PULLO": {}, "PUYUSCA": {}, "SAN FRANCISCO DE RAVACAYCO": {}, "UPAHUACHO": {}
        },
        "PAUCAR DEL SARA SARA": {
            "COLTA": {}, "CORCULLA": {}, "LAMPA": {}, "MARCABAMBA": {}, "OYOLO": {}, "PARARCA": {}, "PAUSA": {}, "SAN JAVIER DE ALPABAMBA": {}, "SAN JOSE DE USHUA": {}, "SARA SARA": {}
        },
        "SUCRE": {
            "BELEN": {}, "CHALCOS": {}, "CHILCAYOC": {}, "HUACAÑA": {}, "MORCOLLA": {}, "PAICO": {}, "QUEROBAMBA": {}, "SAN PEDRO DE LARCAY": {}, "SAN SALVADOR DE QUIJE": {}, "SANTIAGO DE PAUCARAY": {}, "SORAS": {}
        },
        "VICTOR FAJARDO": {
            "ALCAMENCA": {}, "APONGO": {}, "ASQUIPATA": {}, "CANARIA": {}, "CAYARA": {}, "COLCA": {}, "HUAMANQUIQUIA": {}, "HUANCAPI": {}, "HUANCARAYLLA": {}, "HUAYA": {}, "SARHUA": {}, "VILCANCHOS": {}
        },
        "VILCAS HUAMAN": {
            "ACCOMARCA": {}, "CARHUANCA": {}, "CONCEPCION": {}, "HUAMBALPA": {}, "INDEPENDENCIA": {}, "SAURAMA": {}, "VILCAS HUAMAN": {}, "VISCHONGO": {}
        }
    },
    "CAJAMARCA": {
        "CAJABAMBA": {
            "CACHACHI": {}, "CAJABAMBA": {}, "CONDEBAMBA": {}, "SITACOCHA": {}
        },
        "CAJAMARCA": {
            "ASUNCION": {}, "CAJAMARCA": {}, "CHETILLA": {}, "COSPAN": {}, "ENCAÑADA": {}, "JESUS": {}, "LLACANORA": {}, "LOS BAÑOS DEL INCA": {}, "MAGDALENA": {}, "MATARA": {}, "NAMORA": {}, "SAN JUAN": {}
        },
        "CELENDIN": {
            "CELENDIN": {}, "CHUMUCH": {}, "CORTEGANA": {}, "HUASMIN": {}, "JORGE CHAVEZ": {}, "JOSE GALVEZ": {}, "LA LIBERTAD DE PALLAN": {}, "MIGUEL IGLESIAS": {}, "OXAMARCA": {}, "SOROCHUCO": {}, "SUCRE": {}, "UTCO": {}
        },
        "CHOTA": {
            "ANGUIA": {}, "CHADIN": {}, "CHALAMARCA": {}, "CHIGUIRIP": {}, "CHIMBAN": {}, "CHOROPAMPA": {}, "CHOTA": {}, "COCHABAMBA": {}, "CONCHAN": {}, "HUAMBOS": {}, "LAJAS": {}, "LLAMA": {}, "MIRACOSTA": {}, "PACCHA": {}, "PION": {}, "QUEROCOTO": {}, "SAN JUAN DE LICUPIS": {}, "TACABAMBA": {}, "TOCMOCHE": {}
        },
        "CONTUMAZA": {
            "CHILETE": {}, "CONTUMAZA": {}, "CUPISNIQUE": {}, "GUZMANGO": {}, "SAN BENITO": {}, "SANTA CRUZ DE TOLEDO": {}, "TANTARICA": {}, "YONAN": {}
        },
        "CUTERVO": {
            "CALLAYUC": {}, "CHOROS": {}, "CUJILLO": {}, "CUTERVO": {}, "LA RAMADA": {}, "PIMPINGOS": {}, "QUEROCOTILLO": {}, "SAN ANDRES DE CUTERVO": {}, "SAN JUAN DE CUTERVO": {}, "SAN LUIS DE LUCMA": {}, "SANTA CRUZ": {}, "SANTO DOMINGO DE LA CAPILLA": {}, "SANTO TOMAS": {}, "SOCOTA": {}, "TORIBIO CASANOVA": {}
        },
        "HUALGAYOC": {
            "BAMBAMARCA": {}, "CHUGUR": {}, "HUALGAYOC": {}
        },
        "JAEN": {
            "BELLAVISTA": {}, "CHONTALI": {}, "COLASAY": {}, "HUABAL": {}, "JAEN": {}, "LAS PIRIAS": {}, "POMAHUACA": {}, "PUCARA": {}, "SALLIQUE": {}, "SAN FELIPE": {}, "SAN JOSE DEL ALTO": {}, "SANTA ROSA": {}
        },
        "SAN IGNACIO": {
            "CHIRINOS": {}, "HUARANGO": {}, "LA COIPA": {}, "NAMBALLE": {}, "SAN IGNACIO": {}, "SAN JOSE DE LOURDES": {}, "TABACONAS": {}
        },
        "SAN MARCOS": {
            "CHANCAY": {}, "EDUARDO VILLANUEVA": {}, "GREGORIO PITA": {}, "ICHOCAN": {}, "JOSE MANUEL QUIROZ": {}, "JOSE SABOGAL": {}, "PEDRO GALVEZ": {}
        },
        "SAN MIGUEL": {
            "BOLIVAR": {}, "CALQUIS": {}, "CATILLUC": {}, "EL PRADO": {}, "LA FLORIDA": {}, "LLAPA": {}, "NANCHOC": {}, "NIEPOS": {}, "SAN GREGORIO": {}, "SAN MIGUEL": {}, "SAN SILVESTRE DE COCHAN": {}, "TONGOD": {}, "UNION AGUA BLANCA": {}
        },
        "SAN PABLO": {
            "SAN BERNARDINO": {}, "SAN LUIS": {}, "SAN PABLO": {}, "TUMBADEN": {}
        },
        "SANTA CRUZ": {
            "ANDABAMBA": {}, "CATACHE": {}, "CHANCAYBAÑOS": {}, "LA ESPERANZA": {}, "NINABAMBA": {}, "PULAN": {}, "SANTA CRUZ": {}, "SAUCEPAMPA": {}, "SEXI": {}, "UTICYACU": {}, "YAUYUCAN": {}
        }
    },
    "CALLAO": {
        "CALLAO": {
            "BELLAVISTA": {}, "CALLAO": {}, "CARMEN DE LA LEGUA REYNOSO": {}, "LA PERLA": {}, "LA PUNTA": {}, "MI PERU": {}, "VENTANILLA": {}
        }
    },
    "CUSCO": {
        "ACOMAYO": {
            "ACOMAYO": {}, "ACOPIA": {}, "ACOS": {}, "MOSOC LLACTA": {}, "POMACANCHI": {}, "RONDOCAN": {}, "SANGARARA": {}
        },
        "ANTA": {
            "ANCAHUASI": {}, "ANTA": {}, "CACHIMAYO": {}, "CHINCHAYPUJIO": {}, "HUAROCONDO": {}, "LIMATAMBO": {}, "MOLLEPATA": {}, "PUCYURA": {}, "ZURITE": {}
        },
        "CALCA": {
            "CALCA": {}, "COYA": {}, "LAMAY": {}, "LARES": {}, "PISAC": {}, "SAN SALVADOR": {}, "TARAY": {}, "YANATILE": {}
        },
        "CANAS": {
            "CHECCA": {}, "KUNTURKANKI": {}, "LANGUI": {}, "LAYO": {}, "PAMPAMARCA": {}, "QUEHUE": {}, "TUPAC AMARU": {}, "YANAOCA": {}
        },
        "CANCHIS": {
            "CHECACUPE": {}, "COMBAPATA": {}, "MARANGANI": {}, "PITUMARCA": {}, "SAN PABLO": {}, "SAN PEDRO": {}, "SICUANI": {}, "TINTA": {}
        },
        "CHUMBIVILCAS": {
            "CAPACMARCA": {}, "CHAMACA": {}, "COLQUEMARCA": {}, "LIVITACA": {}, "LLUSCO": {}, "QUIÑOTA": {}, "SANTO TOMAS": {}, "VELILLE": {}
        },
        "CUSCO": {
            "CCORCA": {}, "CUSCO": {}, "POROY": {}, "SAN JERONIMO": {}, "SAN SEBASTIAN": {}, "SANTIAGO": {}, "SAYLLA": {}, "WANCHAQ": {}
        },
        "ESPINAR": {
            "ALTO PICHIGUA": {}, "CONDOROMA": {}, "COPORAQUE": {}, "ESPINAR": {}, "OCORURO": {}, "PALLPATA": {}, "PICHIGUA": {}, "SUYCKUTAMBO": {}
        },
        "LA CONVENCION": {
            "CIELO PUNCO": {}, "ECHARATE": {}, "HUAYOPATA": {}, "INKAWASI": {}, "KUMPIRUSHIATO": {}, "MANITEA": {}, "MARANURA": {}, "MEGANTONI": {}, "OCOBAMBA": {}, "PICHARI": {}, "QUELLOUNO": {}, "QUIMBIRI": {}, "SANTA ANA": {}, "SANTA TERESA": {}, "UNION ASHÁNINKA": {}, "VILCABAMBA": {}, "VILLA KINTIARINA": {}, "VILLA VIRGEN": {}
        },
        "PARURO": {
            "ACCHA": {}, "CCAPI": {}, "COLCHA": {}, "HUANOQUITE": {}, "OMACHA": {}, "PACCARITAMBO": {}, "PARURO": {}, "PILLPINTO": {}, "YAURISQUE": {}
        },
        "PAUCARTAMBO": {
            "CAICAY": {}, "CHALLABAMBA": {}, "COLQUEPATA": {}, "HUANCARANI": {}, "KOSÑIPATA": {}, "PAUCARTAMBO": {}
        },
        "QUISPICANCHI": {
            "ANDAHUAYLILLAS": {}, "CAMANTI": {}, "CCARHUAYO": {}, "CCATCA": {}, "CUSIPATA": {}, "HUARO": {}, "LUCRE": {}, "MARCAPATA": {}, "OCONGATE": {}, "OROPESA": {}, "QUIQUIJANA": {}, "URCOS": {}
        },
        "URUBAMBA": {
            "CHINCHERO": {}, "HUAYLLABAMBA": {}, "MACHUPICCHU": {}, "MARAS": {}, "OLLANTAYTAMBO": {}, "URUBAMBA": {}, "YUCAY": {}
        }
    },
    "HUANCAVELICA": {
        "ACOBAMBA": {
            "ACOBAMBA": {}, "ANDABAMBA": {}, "ANTA": {}, "CAJA": {}, "MARCAS": {}, "PAUCARA": {}, "POMACOCHA": {}, "ROSARIO": {}
        },
        "ANGARAES": {
            "ANCHONGA": {}, "CALLANMARCA": {}, "CCOCHACCASA": {}, "CHINCHO": {}, "CONGALLA": {}, "HUANCA-HUANCA": {}, "HUAYLLAY GRANDE": {}, "JULCAMARCA": {}, "LIRCAY": {}, "SAN ANTONIO DE ANTAPARCO": {}, "SANTO TOMAS DE PATA": {}, "SECCLLA": {}
        },
        "CASTROVIRREYNA": {
            "ARMA": {}, "AURAHUA": {}, "CAPILLAS": {}, "CASTROVIRREYNA": {}, "CHUPAMARCA": {}, "COCAS": {}, "HUACHOS": {}, "HUAMATAMBO": {}, "MOLLEPAMPA": {}, "SAN JUAN": {}, "SANTA ANA": {}, "TANTARA": {}, "TICRAPO": {}
        },
        "CHURCAMPA": {
            "ANCO": {}, "CHINCHIHUASI": {}, "CHURCAMPA": {}, "COSME": {}, "EL CARMEN": {}, "LA MERCED": {}, "LOCROJA": {}, "PACHAMARCA": {}, "PAUCARBAMBA": {}, "SAN MIGUEL DE MAYOCC": {}, "SAN PEDRO DE CORIS": {}
        },
        "HUANCAVELICA": {
            "ACOBAMBILLA": {}, "ACORIA": {}, "ASCENSION": {}, "CONAYCA": {}, "CUENCA": {}, "HUACHOCOLPA": {}, "HUANCAVELICA": {}, "HUANDO": {}, "HUAYLLAHUARA": {}, "IZCUCHACA": {}, "LARIA": {}, "MANTA": {}, "MARISCAL CACERES": {}, "MOYA": {}, "NUEVO OCCORO": {}, "PALCA": {}, "PILCHACA": {}, "VILCA": {}, "YAULI": {}
        },
        "HUAYTARA": {
            "AYAVI": {}, "CORDOVA": {}, "HUAYACUNDO ARMA": {}, "HUAYTARA": {}, "LARAMARCA": {}, "OCOYO": {}, "PILPICHACA": {}, "QUERCO": {}, "QUITO-ARMA": {}, "SAN ANTONIO DE CUSICANCHA": {}, "SAN FRANCISCO DE SANGAYAICO": {}, "SAN ISIDRO": {}, "SANTIAGO DE CHOCORVOS": {}, "SANTIAGO DE QUIRAHUARA": {}, "SANTO DOMINGO DE CAPILLAS": {}, "TAMBO": {}
        },
        "TAYACAJA": {
            "ACOSTAMBO": {}, "ACRAQUIA": {}, "AHUAYCHA": {}, "ANDAYMARCA": {}, "COCHABAMBA": {}, "COLCABAMBA": {}, "DANIEL HERNANDEZ": {}, "HUACHOCOLPA": {}, "HUARIBAMBA": {}, "LAMBRAS": {}, "ÑAHUIMPUQUIO": {}, "PAMPAS": {}, "PAZOS": {}, "PICHOS": {}, "QUICHUAS": {}, "QUISHUAR": {}, "ROBLE": {}, "SALCABAMBA": {}, "SALCAHUASI": {}, "SAN MARCOS DE ROCCHAC": {}, "SANTIAGO DE TUCUMA": {}, "SURCUBAMBA": {}, "TINTAY PUNCU": {}
        }
    },
    "HUANUCO": {
        "AMBO": {
            "AMBO": {}, "CAYNA": {}, "COLPAS": {}, "CONCHAMARCA": {}, "HUACAR": {}, "SAN FRANCISCO": {}, "SAN RAFAEL": {}, "TOMAY KICHWA": {}
        },
        "DOS DE MAYO": {
            "CHUQUIS": {}, "LA UNION": {}, "MARIAS": {}, "PACHAS": {}, "QUIVILLA": {}, "RIPAN": {}, "SHUNQUI": {}, "SILLAPATA": {}, "YANAS": {}
        },
        "HUACAYBAMBA": {
            "CANCHABAMBA": {}, "COCHABAMBA": {}, "HUACAYBAMBA": {}, "PINRA": {}
        },
        "HUAMALIES": {
            "ARANCAY": {}, "CHAVIN DE PARIARCA": {}, "JACAS GRANDE": {}, "JIRCAN": {}, "LLATA": {}, "MIRAFLORES": {}, "MONZON": {}, "PUNCHAO": {}, "PUÑOS": {}, "SINGA": {}, "TANTAMAYO": {}
        },
        "HUANUCO": {
            "AMARILIS": {}, "CHINCHAO": {}, "CHURUBAMBA": {}, "HUANUCO": {}, "MARGOS": {}, "PILLCO MARCA": {}, "QUISQUI": {}, "SAN FRANCISCO DE CAYRAN": {}, "SAN PABLO DE PILLAO": {}, "SAN PEDRO DE CHAULAN": {}, "SANTA MARIA DEL VALLE": {}, "YACUS": {}, "YARUMAYO": {}
        },
        "LAURICOCHA": {
            "BAÑOS": {}, "JESUS": {}, "JIVIA": {}, "QUEROPALCA": {}, "RONDOS": {}, "SAN FRANCISCO DE ASIS": {}, "SAN MIGUEL DE CAURI": {}
        },
        "LEONCIO PRADO": {
            "CASTILLO GRANDE": {}, "DANIEL ALOMIAS ROBLES": {}, "HERMILIO VALDIZAN": {}, "JOSE CRESPO Y CASTILLO": {}, "LUYANDO": {}, "MARIANO DAMASO BERAUN": {}, "PUCAYACU": {}, "PUEBLO NUEVO": {}, "RUPA-RUPA": {}, "SANTO DOMINGO DE ANDA": {}
        },
        "MARAÑON": {
            "CHOLON": {}, "HUACRACHUCO": {}, "LA MORADA": {}, "SAN BUENAVENTURA": {}, "SANTA ROSA DE ALTO YANAJANCA": {}
        },
        "PACHITEA": {
            "CHAGLLA": {}, "MOLINO": {}, "PANAO": {}, "UMARI": {}
        },
        "PUERTO INCA": {
            "CODO DEL POZUZO": {}, "HONORIA": {}, "PUERTO INCA": {}, "TOURNAVISTA": {}, "YUYAPICHIS": {}
        },
        "YAROWILCA": {
            "APARICIO POMARES": {}, "CAHUAC": {}, "CHACABAMBA": {}, "CHAVINILLO": {}, "CHORAS": {}, "JACAS CHICO": {}, "OBAS": {}, "PAMPAMARCA": {}
        }
    },
    "ICA": {
        "CHINCHA": {
            "ALTO LARAN": {}, "CHAVIN": {}, "CHINCHA ALTA": {}, "CHINCHA BAJA": {}, "EL CARMEN": {}, "GROCIO PRADO": {}, "PUEBLO NUEVO": {}, "SAN JUAN DE YANAC": {}, "SAN PEDRO DE HUACARPANA": {}, "SUNAMPE": {}, "TAMBO DE MORA": {}
        },
        "ICA": {
            "ICA": {}, "LA TINGUIÑA": {}, "LOS AQUIJES": {}, "OCUCAJE": {}, "PACHACUTEC": {}, "PARCONA": {}, "PUEBLO NUEVO": {}, "SALAS": {}, "SAN JOSE DE LOS MOLINOS": {}, "SAN JUAN BAUTISTA": {}, "SANTIAGO": {}, "SUBTANJALLA": {}, "TATE": {}, "YAUCA DEL ROSARIO": {}
        },
        "NAZCA": {
            "CHANGUILLO": {}, "EL INGENIO": {}, "MARCONA": {}, "NAZCA": {}, "VISTA ALEGRE": {}
        },
        "PALPA": {
            "LLIPATA": {}, "PALPA": {}, "RIO GRANDE": {}, "SANTA CRUZ": {}, "TIBILLO": {}
        },
        "PISCO": {
            "HUANCANO": {}, "HUMAY": {}, "INDEPENDENCIA": {}, "PARACAS": {}, "PISCO": {}, "SAN ANDRES": {}, "SAN CLEMENTE": {}, "TUPAC AMARU INCA": {}
        }
    },
    "JUNIN": {
        "CHANCHAMAYO": {
            "CHANCHAMAYO": {}, "PERENE": {}, "PICHANAQUI": {}, "SAN LUIS DE SHUARO": {}, "SAN RAMON": {}, "VITOC": {}
        },
        "CHUPACA": {
            "AHUAC": {}, "CHONGOS BAJO": {}, "CHUPACA": {}, "HUACHAC": {}, "HUAMANCACA CHICO": {}, "SAN JUAN DE JARPA": {}, "SAN JUAN DE YSCOS": {}, "TRES DE DICIEMBRE": {}, "YANACANCHA": {}
        },
        "CONCEPCION": {
            "ACO": {}, "ANDAMARCA": {}, "CHAMBARA": {}, "COCHAS": {}, "COMAS": {}, "CONCEPCION": {}, "HEROINAS TOLEDO": {}, "MANZANARES": {}, "MARISCAL CASTILLA": {}, "MATAHUASI": {}, "MITO": {}, "NUEVE DE JULIO": {}, "ORCOTUNA": {}, "SAN JOSE DE QUERO": {}, "SANTA ROSA DE OCOPA": {}
        },
        "HUANCAYO": {
            "CARHUACALLANGA": {}, "CHACAPAMPA": {}, "CHICCHE": {}, "CHILCA": {}, "CHONGOS ALTO": {}, "CHUPURO": {}, "COLCA": {}, "CULLHUAS": {}, "EL TAMBO": {}, "HUACRAPUQUIO": {}, "HUALHUAS": {}, "HUANCAN": {}, "HUANCAYO": {}, "HUASICANCHA": {}, "HUAYUCACHI": {}, "INGENIO": {}, "PARIAHUANCA": {}, "PILCOMAYO": {}, "PUCARA": {}, "QUICHUAY": {}, "QUILCAS": {}, "SAN AGUSTIN": {}, "SAN JERONIMO DE TUNAN": {}, "SAÑO": {}, "SANTO DOMINGO DE ACOBAMBA": {}, "SAPALLANGA": {}, "SICAYA": {}, "VIQUES": {}
        },
        "JAUJA": {
            "ACOLLA": {}, "APATA": {}, "ATAURA": {}, "CANCHAYLLO": {}, "CURICACA": {}, "EL MANTARO": {}, "HUAMALI": {}, "HUARIPAMPA": {}, "HUERTAS": {}, "JANJAILLO": {}, "JAUJA": {}, "JULCAN": {}, "LEONOR ORDOÑEZ": {}, "LLOCLLAPAMPA": {}, "MARCO": {}, "MASMA": {}, "MASMA CHICCHE": {}, "MOLINOS": {}, "MONOBAMBA": {}, "MUQUI": {}, "MUQUIYAUYO": {}, "PACA": {}, "PACCHA": {}, "PANCAN": {}, "PARCO": {}, "POMACANCHA": {}, "RICRAN": {}, "SAN LORENZO": {}, "SAN PEDRO DE CHUNAN": {}, "SAUSA": {}, "SINCOS": {}, "TUNAN MARCA": {}, "YAULI": {}, "YAUYOS": {}
        },
        "JUNIN": {
            "CARHUAMAYO": {}, "JUNIN": {}, "ONDORES": {}, "ULCUMAYO": {}
        },
        "SATIPO": {
            "COVIRIALI": {}, "LLAYLLA": {}, "MAZAMARI": {}, "PAMPA HERMOSA": {}, "PANGOA": {}, "RIO NEGRO": {}, "RIO TAMBO": {}, "SATIPO": {}, "VIZCATAN DEL ENE": {}
        },
        "TARMA": {
            "ACOBAMBA": {}, "HUARICOLCA": {}, "HUASAHUASI": {}, "LA UNION": {}, "PALCA": {}, "PALCAMAYO": {}, "SAN PEDRO DE CAJAS": {}, "TAPO": {}, "TARMA": {}
        },
        "YAULI": {
            "CHACAPALPA": {}, "HUAY-HUAY": {}, "LA OROYA": {}, "MARCAPOMACOCHA": {}, "MOROCOCHA": {}, "PACCHA": {}, "SANTA BARBARA DE CARHUACAYAN": {}, "SANTA ROSA DE SACCO": {}, "SUITUCANCHA": {}, "YAULI": {}
        }
    },
    "LA LIBERTAD": {
        "ASCOPE": {
            "ASCOPE": {}, "CASA GRANDE": {}, "CHICAMA": {}, "CHOCOPE": {}, "MAGDALENA DE CAO": {}, "PAIJAN": {}, "RAZURI": {}, "SANTIAGO DE CAO": {}
        },
        "BOLIVAR": {
            "BAMBAMARCA": {}, "BOLIVAR": {}, "CONDORMARCA": {}, "LONGOTEA": {}, "UCHUMARCA": {}, "UCUNCHA": {}
        },
        "CHEPEN": {
            "CHEPEN": {}, "PACANGA": {}, "PUEBLO NUEVO": {}
        },
        "GRAN CHIMU": {
            "CASCAS": {}, "LUCMA": {}, "MARMOT": {}, "SAYAPULLO": {}
        },
        "JULCAN": {
            "CALAMARCA": {}, "CARABAMBA": {}, "HUASO": {}, "JULCAN": {}
        },
        "OTUZCO": {
            "AGALLPAMPA": {}, "CHARAT": {}, "HUARANCHAL": {}, "LA CUESTA": {}, "MACHE": {}, "OTUZCO": {}, "PARANDAY": {}, "SALPO": {}, "SINSICAP": {}, "USQUIL": {}
        },
        "PACASMAYO": {
            "GUADALUPE": {}, "JEQUETEPEQUE": {}, "PACASMAYO": {}, "SAN JOSE": {}, "SAN PEDRO DE LLOC": {}
        },
        "PATAZ": {
            "BULDIBUYO": {}, "CHILLIA": {}, "HUANCASPATA": {}, "HUAYLILLAS": {}, "HUAYO": {}, "ONGON": {}, "PARCOY": {}, "PATAZ": {}, "PIAS": {}, "SANTIAGO DE CHALLAS": {}, "TAURIJA": {}, "TAYABAMBA": {}, "URPAY": {}
        },
        "SANCHEZ CARRION": {
            "CHUGAY": {}, "COCHORCO": {}, "CURGOS": {}, "HUAMACHUCO": {}, "MARCABAL": {}, "SANAGORAN": {}, "SARIN": {}, "SARTIMBAMBA": {}
        },
        "SANTIAGO DE CHUCO": {
            "ANGASMARCA": {}, "CACHICADAN": {}, "MOLLEBAMBA": {}, "MOLLEPATA": {}, "QUIRUVILCA": {}, "SANTA CRUZ DE CHUCA": {}, "SANTIAGO DE CHUCO": {}, "SITABAMBA": {}
        },
        "TRUJILLO": {
            "EL PORVENIR": {}, "ALTO TRUJILLO": {}, "FLORENCIA DE MORA": {}, "HUANCHACO": {}, "LA ESPERANZA": {}, "LAREDO": {}, "MOCHE": {}, "POROTO": {}, "SALAVERRY": {}, "SIMBAL": {}, "TRUJILLO": {}, "VICTOR LARCO HERRERA": {}
        },
        "VIRU": {
            "CHAO": {}, "GUADALUPITO": {}, "VIRU": {}
        }
    },
    "LAMBAYEQUE": {
        "CHICLAYO": {
            "CAYALTI": {}, "CHICLAYO": {}, "CHONGOYAPE": {}, "ETEN": {}, "ETEN PUERTO": {}, "JOSE LEONARDO ORTIZ": {}, "LA VICTORIA": {}, "LAGUNAS": {}, "MONSEFU": {}, "NUEVA ARICA": {}, "OYOTUN": {}, "PATAPO": {}, "PICSI": {}, "PIMENTEL": {}, "POMALCA": {}, "PUCALA": {}, "REQUE": {}, "SAÑA": {}, "SANTA ROSA": {}, "TUMAN": {}
        },
        "FERREÑAFE": {
            "CAÑARIS": {}, "FERREÑAFE": {}, "INCAHUASI": {}, "MANUEL ANTONIO MESONES MURO": {}, "PITIPO": {}, "PUEBLO NUEVO": {}
        },
        "LAMBAYEQUE": {
            "CHOCHOPE": {}, "ILLIMO": {}, "JAYANCA": {}, "LAMBAYEQUE": {}, "MOCHUMI": {}, "MORROPE": {}, "MOTUPE": {}, "OLMOS": {}, "PACORA": {}, "SALAS": {}, "SAN JOSE": {}, "TUCUME": {}
        }
    },
    "LIMA": {
        "BARRANCA": {
            "BARRANCA": {}, "PARAMONGA": {}, "PATIVILCA": {}, "SUPE": {}, "SUPE PUERTO": {}
        },
        "CAJATAMBO": {
            "CAJATAMBO": {}, "COPA": {}, "GORGOR": {}, "HUANCAPON": {}, "MANAS": {}
        },
        "CAÑETE": {
            "ASIA": {}, "CALANGO": {}, "CERRO AZUL": {}, "CHILCA": {}, "COAYLLO": {}, "IMPERIAL": {}, "LUNAHUANA": {}, "MALA": {}, "NUEVO IMPERIAL": {}, "PACARAN": {}, "QUILMANA": {}, "SAN ANTONIO": {}, "SAN LUIS": {}, "SAN VICENTE DE CAÑETE": {}, "SANTA CRUZ DE FLORES": {}, "ZUÑIGA": {}
        },
        "CANTA": {
            "ARAHUAY": {}, "CANTA": {}, "HUAMANTANGA": {}, "HUAROS": {}, "LACHAQUI": {}, "SAN BUENAVENTURA": {}, "SANTA ROSA DE QUIVES": {}
        },
        "HUARAL": {
            "ATAVILLOS ALTO": {}, "ATAVILLOS BAJO": {}, "AUCALLAMA": {}, "CHANCAY": {}, "HUARAL": {}, "IHUARI": {}, "LAMPIAN": {}, "PACARAOS": {}, "SAN MIGUEL DE ACOS": {}, "SANTA CRUZ DE ANDAMARCA": {}, "SUMBILCA": {}, "VEINTISIETE DE NOVIEMBRE": {}
        },
        "HUAROCHIRI": {
            "ANTIOQUIA": {}, "CALLAHUANCA": {}, "CARAMPOMA": {}, "CHICLA": {}, "CUENCA": {}, "HUACHUPAMPA": {}, "HUANZA": {}, "HUAROCHIRI": {}, "LAHUAYTAMBO": {}, "LANGA": {}, "LARAOS": {}, "MARIATANA": {}, "MATUCANA": {}, "RICARDO PALMA": {}, "SAN ANDRES DE TUPICOCHA": {}, "SAN ANTONIO": {}, "SAN BARTOLOME": {}, "SAN DAMIAN": {}, "SAN JUAN DE IRIS": {}, "SAN JUAN DE TANTARANCHE": {}, "SAN LORENZO DE QUINTI": {}, "SAN MATEO": {}, "SAN MATEO DE OTAO": {}, "SAN PEDRO DE CASTA": {}, "SAN PEDRO DE HUANCAYRE": {}, "SANGALLAYA": {}, "SANTA CRUZ DE COCACHACRA": {}, "SANTA EULALIA": {}, "SANTIAGO DE ANCHUCAYA": {}, "SANTIAGO DE TUNA": {}, "SANTO DOMINGO DE LOS OLLEROS": {}, "SURCO": {}
        },
        "HUAURA": {
            "AMBAR": {}, "CALETA DE CARQUIN": {}, "CHECRAS": {}, "HUACHO": {}, "HUALMAY": {}, "HUAURA": {}, "LEONCIO PRADO": {}, "PACCHO": {}, "SANTA LEONOR": {}, "SANTA MARIA": {}, "SAYAN": {}, "VEGUETA": {}
        },
        "LIMA": {
            "ANCON": {}, "ATE": {}, "BARRANCO": {}, "BREÑA": {}, "CARABAYLLO": {}, "CHACLACAYO": {}, "CHORRILLOS": {}, "CIENEGUILLA": {}, "COMAS": {}, "EL AGUSTINO": {}, "INDEPENDENCIA": {}, "JESUS MARIA": {}, "LA MOLINA": {}, "LA VICTORIA": {}, "LIMA": {}, "LINCE": {}, "LOS OLIVOS": {}, "LURIGANCHO": {}, "LURIN": {}, "MAGDALENA DEL MAR": {}, "MIRAFLORES": {}, "PACHACAMAC": {}, "PUCUSANA": {}, "PUEBLO LIBRE": {}, "PUENTE PIEDRA": {}, "PUNTA HERMOSA": {}, "PUNTA NEGRA": {}, "RIMAC": {}, "SAN BARTOLO": {}, "SAN BORJA": {}, "SAN ISIDRO": {}, "SAN JUAN DE LURIGANCHO": {}, "SAN JUAN DE MIRAFLORES": {}, "SAN LUIS": {}, "SAN MARTIN DE PORRES": {}, "SAN MIGUEL": {}, "SANTA ANITA": {}, "SANTA MARIA DE HUACHIPA": {}, "SANTA MARIA DEL MAR": {}, "SANTA ROSA": {}, "SANTIAGO DE SURCO": {}, "SURQUILLO": {}, "VILLA EL SALVADOR": {}, "VILLA MARIA DEL TRIUNFO": {}
        },
        "OYON": {
            "ANDAJES": {}, "CAUJUL": {}, "COCHAMARCA": {}, "NAVAN": {}, "OYON": {}, "PACHANGARA": {}
        },
        "YAUYOS": {
            "ALIS": {}, "AYAUCA": {}, "AYAVIRI": {}, "AZANGARO": {}, "CACRA": {}, "CARANIA": {}, "CATAHUASI": {}, "CHOCOS": {}, "COCHAS": {}, "COLONIA": {}, "HONGOS": {}, "HUAMPARA": {}, "HUANCAYA": {}, "HUAÑEC": {}, "HUANGASCAR": {}, "HUANTAN": {}, "LARAOS": {}, "LINCHA": {}, "MADEAN": {}, "MIRAFLORES": {}, "OMAS": {}, "PUTINZA": {}, "QUINCHES": {}, "QUINOCAY": {}, "SAN JOAQUIN": {}, "SAN PEDRO DE PILAS": {}, "TANTA": {}, "TAURIPAMPA": {}, "TOMAS": {}, "TUPE": {}, "VIÑAC": {}, "VITIS": {}, "YAUYOS": {}
        }
    },
    "LORETO": {
        "ALTO AMAZONAS": {
            "BALSAPUERTO": {}, "JEBEROS": {}, "LAGUNAS": {}, "SANTA CRUZ": {}, "TENIENTE CESAR LOPEZ ROJAS": {}, "YURIMAGUAS": {}
        },
        "DATEM DEL MARAÑON": {
            "ANDOAS": {}, "BARRANCA": {}, "CAHUAPANAS": {}, "MANSERICHE": {}, "MORONA": {}, "PASTAZA": {}
        },
        "LORETO": {
            "NAUTA": {}, "PARINARI": {}, "TIGRE": {}, "TROMPETEROS": {}, "URARINAS": {}
        },
        "MARISCAL RAMON CASTILLA": {
            "PEBAS": {}, "RAMON CASTILLA": {}, "SAN PABLO": {}, "YAVARI": {}
        },
        "MAYNAS": {
            "ALTO NANAY": {}, "BELEN": {}, "FERNANDO LORES": {}, "INDIANA": {}, "IQUITOS": {}, "LAS AMAZONAS": {}, "MAZAN": {}, "NAPO": {}, "PUNCHANA": {}, "PUTUMAYO": {}, "SAN JUAN BAUTISTA": {}, "TENIENTE MANUEL CLAVERO": {}, "TORRES CAUSANA": {}
        },
        "PUTUMAYO": {
            "PUTUMAYO": {}, "ROSA PANDURO": {}, "TENIENTE MANUEL CLAVERO": {}, "YAGUAS": {}
        },
        "REQUENA": {
            "ALTO TAPICHE": {}, "CAPELO": {}, "EMILIO SAN MARTIN": {}, "JENARO HERRERA": {}, "MAQUIA": {}, "PUINAHUA": {}, "REQUENA": {}, "SAQUENA": {}, "SOPLIN": {}, "TAPICHE": {}, "YAQUERANA": {}
        },
        "UCAYALI": {
            "CONTAMANA": {}, "INAHUAYA": {}, "PADRE MARQUEZ": {}, "PAMPA HERMOSA": {}, "SARAYACU": {}, "VARGAS GUERRA": {}
        }
    },
    "MADRE DE DIOS": {
        "MANU": {
            "FITZCARRALD": {}, "HUEPETUHE": {}, "MADRE DE DIOS": {}, "MANU": {}
        },
        "TAHUAMANU": {
            "IBERIA": {}, "IÑAPARI": {}, "TAHUAMANU": {}
        },
        "TAMBOPATA": {
            "INAMBARI": {}, "LABERINTO": {}, "LAS PIEDRAS": {}, "TAMBOPATA": {}
        }
    },
    "MOQUEGUA": {
        "GENERAL SANCHEZ CERRO": {
            "CHOJATA": {}, "COALAQUE": {}, "ICHUÑA": {}, "LA CAPILLA": {}, "LLOQUE": {}, "MATALAQUE": {}, "OMATE": {}, "PUQUINA": {}, "QUINISTAQUILLAS": {}, "UBINAS": {}, "YUNGA": {}
        },
        "ILO": {
            "EL ALGARROBAL": {}, "ILO": {}, "PACOCHA": {}
        },
        "MARISCAL NIETO": {
            "CARUMAS": {}, "CUCHUMBAYA": {}, "MOQUEGUA": {}, "SAMEGUA": {}, "SAN ANTONIO": {}, "SAN CRISTOBAL": {}, "TORATA": {}
        }
    },
    "PASCO": {
        "DANIEL ALCIDES CARRION": {
            "CHACAYAN": {}, "GOYLLARISQUIZGA": {}, "PAUCAR": {}, "SAN PEDRO DE PILLAO": {}, "SANTA ANA DE TUSI": {}, "TAPUC": {}, "VILCABAMBA": {}, "YANAHUANCA": {}
        },
        "OXAPAMPA": {
            "CHONTABAMBA": {}, "CONSTITUCION": {}, "HUANCABAMBA": {}, "OXAPAMPA": {}, "PALCAZU": {}, "POZUZO": {}, "PUERTO BERMUDEZ": {}, "VILLA RICA": {}
        },
        "PASCO": {
            "CHAUPIMARCA": {}, "HUACHON": {}, "HUARIACA": {}, "HUAYLLAY": {}, "NINACACA": {}, "PALLANCHACRA": {}, "PAUCARTAMBO": {}, "SAN FRANCISCO DE ASIS DE YARUSYACAN": {}, "SIMON BOLIVAR": {}, "TICLACAYAN": {}, "TINYAHUARCO": {}, "VICCO": {}, "YANACANCHA": {}
        }
    },
    "PIURA": {
        "AYABACA": {
            "AYABACA": {}, "FRIAS": {}, "JILILI": {}, "LAGUNAS": {}, "MONTERO": {}, "PACAIPAMPA": {}, "PAIMAS": {}, "SAPILLICA": {}, "SICCHEZ": {}, "SUYO": {}
        },
        "HUANCABAMBA": {
            "CANCHAQUE": {}, "EL CARMEN DE LA FRONTERA": {}, "HUANCABAMBA": {}, "HUARMACA": {}, "LALAQUIZ": {}, "SAN MIGUEL DE EL FAIQUE": {}, "SONDOR": {}, "SONDORILLO": {}
        },
        "MORROPON": {
            "BUENOS AIRES": {}, "CHALACO": {}, "CHULUCANAS": {}, "LA MATANZA": {}, "MORROPON": {}, "SALITRAL": {}, "SAN JUAN DE BIGOTE": {}, "SANTA CATALINA DE MOSSA": {}, "SANTO DOMINGO": {}, "YAMANGO": {}
        },
        "PAITA": {
            "AMOTAPE": {}, "ARENAL": {}, "COLAN": {}, "LA HUACA": {}, "PAITA": {}, "TAMARINDO": {}, "VICHAYAL": {}
        },
        "PIURA": {
            "CASTILLA": {}, "CATACAOS": {}, "CURA MORI": {}, "EL TALLAN": {}, "LA ARENA": {}, "LA UNION": {}, "LAS LOMAS": {}, "PIURA": {}, "TAMBO GRANDE": {}, "VEINTISEIS DE OCTUBRE": {}
        },
        "SECHURA": {
            "BELLAVISTA DE LA UNION": {}, "BERNAL": {}, "CRISTO NOS VALGA": {}, "RINCONADA LLICUAR": {}, "SECHURA": {}, "VICE": {}
        },
        "SULLANA": {
            "BELLAVISTA": {}, "IGNACIO ESCUDERO": {}, "LANCONES": {}, "MARCAVELICA": {}, "MIGUEL CHECA": {}, "QUERECOTILLO": {}, "SALITRAL": {}, "SULLANA": {}
        },
        "TALARA": {
            "EL ALTO": {}, "LA BREA": {}, "LOBITOS": {}, "LOS ORGANOS": {}, "MANCORA": {}, "PARIÑAS": {}
        }
    },
    "PUNO": {
        "AZANGARO": {
            "ACHAYA": {}, "ARAPA": {}, "ASILLO": {}, "AZANGARO": {}, "CAMINACA": {}, "CHUPA": {}, "JOSE DOMINGO CHOQUEHUANCA": {}, "MUÑANI": {}, "POTONI": {}, "SAMAN": {}, "SAN ANTON": {}, "SAN JOSE": {}, "SAN JUAN DE SALINAS": {}, "SANTIAGO DE PUPUJA": {}, "TIRAPATA": {}
        },
        "CARABAYA": {
            "AJOYANI": {}, "AYAPATA": {}, "COASA": {}, "CORANI": {}, "CRUCERO": {}, "ITUATA": {}, "MACUSANI": {}, "OLLACHEA": {}, "SAN GABAN": {}, "USICAYOS": {}
        },
        "CHUCUITO": {
            "DESAGUADERO": {}, "HUACULLANI": {}, "JULI": {}, "KELLUYO": {}, "PISACOMA": {}, "POMATA": {}, "ZEPITA": {}
        },
        "EL COLLAO": {
            "CAPAZO": {}, "CONDURIRI": {}, "ILAVE": {}, "PILCUYO": {}, "SANTA ROSA": {}
        },
        "HUANCANE": {
            "COJATA": {}, "HUANCANE": {}, "HUATASANI": {}, "INCHUPALLA": {}, "PUSI": {}, "ROSASPATA": {}, "TARACO": {}, "VILQUE CHICO": {}
        },
        "LAMPA": {
            "CABANILLA": {}, "CALAPUJA": {}, "LAMPA": {}, "NICASIO": {}, "OCUVIRI": {}, "PALCA": {}, "PARATIA": {}, "PUCARA": {}, "SANTA LUCIA": {}, "VILAVILA": {}
        },
        "MELGAR": {
            "ANTAUTA": {}, "AYAVIRI": {}, "CUPI": {}, "LLALLI": {}, "MACARI": {}, "NUÑOA": {}, "ORURILLO": {}, "SANTA ROSA": {}, "UMACHIRI": {}
        },
        "MOHO": {
            "CONIMA": {}, "HUAYRAPATA": {}, "MOHO": {}, "TILALI": {}
        },
        "PUNO": {
            "ACORA": {}, "AMANTANI": {}, "ATUNCOLLA": {}, "CAPACHICA": {}, "CHUCUITO": {}, "COATA": {}, "HUATA": {}, "MAÑAZO": {}, "PAUCARCOLLA": {}, "PICHACANI": {}, "PLATERIA": {}, "PUNO": {}, "SAN ANTONIO": {}, "TIQUILLACA": {}, "VILQUE": {}
        },
        "SAN ANTONIO DE PUTINA": {
            "ANANEA": {}, "PEDRO VILCA APAZA": {}, "PUTINA": {}, "QUILCAPUNCU": {}, "SINA": {}
        },
        "SAN ROMAN": {
            "CABANA": {}, "CABANILLAS": {}, "CARACOTO": {}, "JULIACA": {}, "SAN MIGUEL": {}
        },
        "SANDIA": {
            "ALTO INAMBARI": {}, "CUYOCUYO": {}, "LIMBANI": {}, "PATAMBUCO": {}, "PHARA": {}, "QUIACA": {}, "SAN JUAN DEL ORO": {}, "SAN PEDRO DE PUTINA PUNCO": {}, "SANDIA": {}, "YANAHUAYA": {}
        },
        "YUNGUYO": {
            "ANAPIA": {}, "COPANI": {}, "CUTURAPI": {}, "OLLARAYA": {}, "TINICACHI": {}, "UNICACHI": {}, "YUNGUYO": {}
        }
    },
    "SAN MARTIN": {
        "BELLAVISTA": {
            "ALTO BIAVO": {}, "BAJO BIAVO": {}, "BELLAVISTA": {}, "HUALLAGA": {}, "SAN PABLO": {}, "SAN RAFAEL": {}
        },
        "EL DORADO": {
            "AGUA BLANCA": {}, "SAN JOSE DE SISA": {}, "SAN MARTIN": {}, "SANTA ROSA": {}, "SHATOJA": {}
        },
        "HUALLAGA": {
            "ALTO SAPOSOA": {}, "EL ESLABON": {}, "PISCOYACU": {}, "SACANCHE": {}, "SAPOSOA": {}, "TINGO DE SAPOSOA": {}
        },
        "LAMAS": {
            "ALONSO DE ALVARADO": {}, "BARRANQUITA": {}, "CAYNARACHI": {}, "CUÑUMBUQUI": {}, "LAMAS": {}, "PINTO RECODO": {}, "RUMISAPA": {}, "SAN ROQUE DE CUMBAZA": {}, "SHANAO": {}, "TABALOSOS": {}, "ZAPATERO": {}
        },
        "MARISCAL CACERES": {
            "CAMPANILLA": {}, "HUICUNGO": {}, "JUANJUI": {}, "PACHIZA": {}, "PAJARILLO": {}
        },
        "MOYOBAMBA": {
            "CALZADA": {}, "HABANA": {}, "JEPELACIO": {}, "MOYOBAMBA": {}, "SORITOR": {}, "YANTALO": {}
        },
        "PICOTA": {
            "BUENOS AIRES": {}, "CASPISAPA": {}, "PICOTA": {}, "PILLUANA": {}, "PUCACACA": {}, "SAN CRISTOBAL": {}, "SAN HILARION": {}, "SHAMBOYACU": {}, "TINGO DE PONASA": {}, "TRES UNIDOS": {}
        },
        "RIOJA": {
            "AWAJUN": {}, "ELIAS SOPLIN VARGAS": {}, "NUEVA CAJAMARCA": {}, "PARDO MIGUEL": {}, "POSIC": {}, "RIOJA": {}, "SAN FERNANDO": {}, "YORONGOS": {}, "YURACYACU": {}
        },
        "SAN MARTIN": {
            "ALBERTO LEVEAU": {}, "CACATACHI": {}, "CHAZUTA": {}, "CHIPURANA": {}, "EL PORVENIR": {}, "HUIMBAYOC": {}, "JUAN GUERRA": {}, "LA BANDA DE SHILCAYO": {}, "MORALES": {}, "PAPAPLAYA": {}, "SAN ANTONIO": {}, "SAUCE": {}, "SHAPAJA": {}, "TARAPOTO": {}
        },
        "TOCACHE": {
            "NUEVO PROGRESO": {}, "POLVORA": {}, "SANTA LUCIA": {}, "SHUNTE": {}, "TOCACHE": {}, "UCHIZA": {}
        }
    },
    "TACNA": {
        "CANDARAVE": {
            "CAIRANI": {}, "CAMILACA": {}, "CANDARAVE": {}, "CURIBAYA": {}, "HUANUARA": {}, "QUILAHUANI": {}
        },
        "JORGE BASADRE": {
            "ILABAYA": {}, "ITE": {}, "LOCUMBA": {}
        },
        "TACNA": {
            "ALTO DE LA ALIANZA": {}, "CALANA": {}, "CIUDAD NUEVA": {}, "CORONEL GREGORIO ALBARRACIN LANCHIP": {}, "INCLAN": {}, "LA YARADA LOS PALOS": {}, "PACHIA": {}, "PALCA": {}, "POCOLLAY": {}, "SAMA": {}, "TACNA": {}
        },
        "TARATA": {
            "CHUCATAMANI": {}, "ESTIQUE": {}, "ESTIQUE-PAMPA": {}, "SITAJARA": {}, "SUSAPAYA": {}, "TARATA": {}, "TARUCACHI": {}, "TICACO": {}
        }
    },
    "TUMBES": {
        "CONTRALMIRANTE VILLAR": {
            "CANOAS DE PUNTA SAL": {}, "CASITAS": {}, "ZORRITOS": {}
        },
        "TUMBES": {
            "CORRALES": {}, "LA CRUZ": {}, "PAMPAS DE HOSPITAL": {}, "SAN JACINTO": {}, "SAN JUAN DE LA VIRGEN": {}, "TUMBES": {}
        },
        "ZARUMILLA": {
            "AGUAS VERDES": {}, "MATAPALO": {}, "PAPAYAL": {}, "ZARUMILLA": {}
        }
    },
    "UCAYALI": {
        "ATALAYA": {
            "RAYMONDI": {}, "SEPAHUA": {}, "TAHUANIA": {}, "YURUA": {}
        },
        "CORONEL PORTILLO": {
            "CALLERIA": {}, "CAMPOVERDE": {}, "IPARIA": {}, "MANANTAY": {}, "MASISEA": {}, "NUEVA REQUENA": {}, "YARINACOCHA": {}
        },
        "PADRE ABAD": {
            "ALEXANDER VON HUMBOLDT": {}, "BOQUERON": {}, "CURIMANA": {}, "HUIPOCA": {}, "IRAZOLA": {}, "NESHUYA": {}, "PADRE ABAD": {}
        },
        "PURUS": {
            "PURUS": {}
        }
    }
};

// ==========================================
// FIN DE DATA GIGANTE
// ==========================================

const depNatural = ref('');
const provNatural = ref('');
const distNatural = ref('');

const departamentos = computed(() => Object.keys(ubigeosData).sort());

const provincias = computed(() => {
    if (depNatural.value && ubigeosData[depNatural.value]) {
        return Object.keys(ubigeosData[depNatural.value]).sort();
    }
    return [];
});

const distritos = computed(() => {
    if (provNatural.value && depNatural.value && ubigeosData[depNatural.value][provNatural.value]) {
        return Object.keys(ubigeosData[depNatural.value][provNatural.value]).sort();
    }
    return [];
});

const actualizarDomicilio = () => {
    let textoActual = form.natural.domicilio || '';
    let direccion = '';
    
    // Rescatamos lo que el asesor escribió manualmente (antes del primer guion)
    if (textoActual.includes(' - ')) {
        direccion = textoActual.substring(0, textoActual.indexOf(' - ')).trim();
    } else {
        direccion = textoActual.trim();
    }

    // Si la dirección escrita era solo un departamento (ej. si apenas va armando el select), la borramos
    if (departamentos.value.includes(direccion.toUpperCase())) {
        direccion = '';
    }

    let partesUbigeo = [];
    if (depNatural.value) partesUbigeo.push(depNatural.value);
    if (provNatural.value) partesUbigeo.push(provNatural.value);
    if (distNatural.value) partesUbigeo.push(distNatural.value);

    // Escribimos mágicamente en la caja de texto
    if (partesUbigeo.length > 0) {
        form.natural.domicilio = (direccion ? direccion + ' - ' : '') + partesUbigeo.join(' - ');
    }
};

const cambioDepartamento = () => {
    provNatural.value = '';
    distNatural.value = '';
    actualizarDomicilio();
};

const cambioProvincia = () => {
    distNatural.value = '';
    actualizarDomicilio();
};

const cambioDistrito = () => {
    actualizarDomicilio();
};

// -----------------------------------

// --- LÓGICA DE UBIGEOS DINÁMICOS (CO-PROPIEDAD) ---
const depCopropiedad = ref('');
const provCopropiedad = ref('');
const distCopropiedad = ref('');

const provinciasCopropiedad = computed(() => {
    if (depCopropiedad.value && ubigeosData[depCopropiedad.value]) {
        return Object.keys(ubigeosData[depCopropiedad.value]).sort();
    }
    return [];
});

const distritosCopropiedad = computed(() => {
    if (provCopropiedad.value && depCopropiedad.value && ubigeosData[depCopropiedad.value][provCopropiedad.value]) {
        return Object.keys(ubigeosData[depCopropiedad.value][provCopropiedad.value]).sort();
    }
    return [];
});

const actualizarDomicilioCopropiedad = () => {
    let textoActual = form.copropiedad.domicilio || '';
    let direccion = '';
    
    if (textoActual.includes(' - ')) {
        direccion = textoActual.substring(0, textoActual.indexOf(' - ')).trim();
    } else {
        direccion = textoActual.trim();
    }

    if (departamentos.value.includes(direccion.toUpperCase())) {
        direccion = '';
    }

    let partesUbigeo = [];
    if (depCopropiedad.value) partesUbigeo.push(depCopropiedad.value);
    if (provCopropiedad.value) partesUbigeo.push(provCopropiedad.value);
    if (distCopropiedad.value) partesUbigeo.push(distCopropiedad.value);

    if (partesUbigeo.length > 0) {
        form.copropiedad.domicilio = (direccion ? direccion + ' - ' : '') + partesUbigeo.join(' - ');
    }
};

const cambioDepartamentoCopropiedad = () => {
    provCopropiedad.value = '';
    distCopropiedad.value = '';
    actualizarDomicilioCopropiedad();
};

const cambioProvinciaCopropiedad = () => {
    distCopropiedad.value = '';
    actualizarDomicilioCopropiedad();
};

const cambioDistritoCopropiedad = () => {
    actualizarDomicilioCopropiedad();
};
// --------------------------------------------------

onMounted(() => {
    const intervalo = setInterval(() => {
        if (contadorModal.value > 0) {
            contadorModal.value--;
        } else {
            clearInterval(intervalo);
        }
    }, 1000);
});

const cerrarModal = () => {
    if (contadorModal.value === 0) {
        mostrarModal.value = false;
    }
};

// --- LÓGICA DEL BUSCADOR DE MODELOS ---
const mostrarDropdown = ref(false);

const listaModelos = [
    '_____________', 'FORTUNER', 'AGYA', 'HILUX', 'YARIS' , 'YARIS CROSS', 'COROLLA', 'COROLLA CROSS', '4RUNNER', 'RAIZE', 'RUSH', 'ETIOS', 'AVENSIS', 'AURIS', 'CAMRY', 'PRIUS', '86', 'AVANZA', 'FJ CRUISER', 'LAND CRUISER PRADO', 'RAV4', 'LC 200', '4RUNNER', 'DUTRO', 'FC', 'FT', 'FG', 'GH', 'FD', 'FC BUS', 'FM', 'C-HR', 'HIACE'
];

const modelosFiltrados = computed(() => {
    if (!form.vehiculo.modelo) return listaModelos;
    return listaModelos.filter(m => m.toLowerCase().includes(form.vehiculo.modelo.toLowerCase()));
});

const seleccionarModelo = (modeloSeleccionado) => {
    form.vehiculo.modelo = modeloSeleccionado;
    mostrarDropdown.value = false;
};

const validarModelo = () => {
    setTimeout(() => {
        mostrarDropdown.value = false;
        if (form.vehiculo.modelo) {
            const modeloValido = listaModelos.find(
                m => m.toUpperCase() === form.vehiculo.modelo.toUpperCase()
            );
            if (modeloValido) {
                form.vehiculo.modelo = modeloValido;
            } else {
                form.vehiculo.modelo = '';
            }
        }
    }, 150);
};

// --- LÓGICA DEL BUSCADOR DE PROVINCIAS REGISTRALES ---
const mostrarDropdownProvincia = ref(false);

const listaProvincias = [
    'TRUJILLO', 'LIMA', 'PIURA', 'CHICLAYO', 'MOYOBAMBA', 'IQUITOS', 'PUCALLPA', 
    'HUARAZ', 'HUANCAYO', 'ICA', 'AREQUIPA', 'TACNA', 'AYACUCHO', 'CHACHAPOYAS', 
    'BAGUA', 'BAGUA GRANDE', 'CHIMBOTE', 'CASMA', 'NUEVO CHIMBOTE', 'ABANCAY', 
    'ANDAHUAYLAS', 'CAMANÁ', 'ISLA-MOLLENDO', 'CASTILLA-APLAO', 'LAMBRAMANI', 
    'HUANTA', 'CAJAMARCA', 'CHOTA', 'JAÉN', 'CALLAO', 'QUILLABAMBA', 'SICUANI', 
    'ESPINAR', 'URUBAMBA', 'HUANCAVELICA', 'HUÁNUCO', 'TINGO MARÍA', 'PISCO', 
    'CHINCHA', 'NASCA', 'LA MERCED', 'SATIPO', 'TARMA', 'SAN PEDRO DE LLOC', 
    'CHEPÉN', 'OTUZCO', 'HUAMACHUCO', 'CHOCOPE', 'SAN ISIDRO', 'MIRAFLORES', 
    'SAN MIGUEL', 'SURCO', 'LIMA NORTE', 'EL SALVADOR', 'SAN BORJA', 'BARRANCA', 
    'CAÑETE', 'HUACHO', 'HUARAL', 'YURIMAGUAS', 'TAMBOPATA', 'ILO', 'MOQUEGUA', 
    'PASCO', 'SULLANA', 'TALARA', 'JULIACA', 'PUNO', 'TARAPOTO', 'JUANJUÍ', 'TUMBES'
];

const provinciasFiltradas = computed(() => {
    if (!form.juridica.provincia_registral) return listaProvincias;
    return listaProvincias.filter(p => p.toLowerCase().includes(form.juridica.provincia_registral.toLowerCase()));
});

const seleccionarProvincia = (provinciaSeleccionada) => {
    form.juridica.provincia_registral = provinciaSeleccionada;
    mostrarDropdownProvincia.value = false;
};

const validarProvincia = () => {
    setTimeout(() => {
        mostrarDropdownProvincia.value = false;
        if (form.juridica.provincia_registral) {
            const provinciaValida = listaProvincias.find(
                p => p.toUpperCase() === form.juridica.provincia_registral.toUpperCase()
            );
            if (provinciaValida) {
                form.juridica.provincia_registral = provinciaValida;
            } else {
                form.juridica.provincia_registral = ''; 
            }
        }
    }, 150);
};

const form = useForm({
    ciudad: '',
    stock: '',
    fecha: fechaHoy,
    tipo_cliente: '',
    juridica: {
        ruc: '',
        nombre_empresa: '',
        tipo_doc_representante: '',
        dni_representante: '',
        nombre_representante: '',
        partida: '',
        provincia_registral: ''
    },
    natural: {
        tipo_doc: '',
        dni: '',
        nombre: '',
        estado_civil: '',
        tipo_doc_conyuge: '',
        dni_conyuge: '',
        nombre_conyuge: '',
        domicilio: '',
        union_hecho: 'NO',
        tipo_doc_union: 'DNI',
        dni_union: '',
        nombre_union: '',
        partida_union: '',
        sede_union: '',
        bienes_separados: 'NO',
        partida_bienes: '',
        sede_bienes: ''
    },
    copropiedad: {
        domicilio: '',
        lista: [
            {
                tipo_doc: '',
                dni: '',
                nombre: '',
                estado_civil: '',
                tipo_doc_conyuge: '',
                dni_conyuge: '',
                nombre_conyuge: ''
            }
        ]
    },
    vehiculo: {
        marca: '',
        modelo: '',
        serie_chasis: '',
        motor: ''
    }
});

const limpiarDocumento = (objeto, campoNumero, campoTipo) => {
    if (objeto[campoTipo] === 'DNI') {
        objeto[campoNumero] = objeto[campoNumero].replace(/\D/g, '').slice(0, 8);
    } else if (objeto[campoTipo] === 'RUC') {
        objeto[campoNumero] = objeto[campoNumero].replace(/\D/g, '').slice(0, 11);
    }
};

const agregarCopropietario = () => {
    form.copropiedad.lista.push({
        tipo_doc: 'DNI',
        dni: '',
        nombre: '',
        estado_civil: 'SOLTERO',
        tipo_doc_conyuge: 'DNI',
        dni_conyuge: '',
        nombre_conyuge: ''
    });
};

const eliminarCopropietario = (index) => {
    form.copropiedad.lista.splice(index, 1);
};

const generarPdf = async () => {
    try {
        const response = await axios.post('/inmatriculacion/generar', form.data(), {
            responseType: 'blob'
        });
        
        let nombreCliente = 'CLIENTE';
        if (form.tipo_cliente === 'Natural') {
            nombreCliente = form.natural.nombre;
        } else if (form.tipo_cliente === 'Juridica') {
            nombreCliente = form.juridica.nombre_empresa;
        } else if (form.tipo_cliente === 'Copropiedad') {
            nombreCliente = form.copropiedad.lista[0].nombre;
            if (form.copropiedad.lista.length > 1) {
                nombreCliente += "_Y_OTROS";
            }
        }

        const nombreLimpio = nombreCliente.trim().replace(/\s+/g, '_').toUpperCase();
        const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `CARTAS_PODER_${nombreLimpio}.pdf`); 
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        window.open(url, '_blank');
        form.reset();

    } catch (error) {
        console.error("Hubo un error al generar el PDF:", error);
        alert("Ocurrió un error. Por favor, revisa que todos los campos obligatorios estén llenos y los DNI tengan 8 dígitos.");
    }
};

const buscarDocumento = async (entidad, campoDoc, campoTipo, campoNombre) => {
    const tipo = entidad[campoTipo];
    const doc = entidad[campoDoc];

    if (tipo === 'DNI' && doc && doc.length === 8) {
        try {
            const respuesta = await axios.get(`/consultar-dni/${doc}`);
            if (respuesta.data && respuesta.data.success) {
                const datos = respuesta.data; 
                entidad[campoNombre] = `${datos.nombres} ${datos.apellidoPaterno} ${datos.apellidoMaterno}`;
            }
        } catch (error) { 
            console.error("Error al buscar DNI:", error); 
        }
    }
    else if (tipo === 'RUC' && doc && doc.length === 11) {
        try {
            const respuesta = await axios.get(`/consultar-ruc/${doc}`);
            if (respuesta.data && respuesta.data.razonSocial) {
                entidad[campoNombre] = respuesta.data.razonSocial;
            }
        } catch (error) { 
            console.error("Error al buscar RUC:", error); 
        }
    }
};
</script>