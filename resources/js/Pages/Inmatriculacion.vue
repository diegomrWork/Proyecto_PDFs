<template>
    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Generador de Cartas de Inmatriculación</h2>

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
                        <input type="date" v-model="form.fecha" class="w-full border-gray-300 rounded-md shadow-sm" required />
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
                                <!-- Como aquí siempre es RUC, forzamos la búsqueda de 11 dígitos -->
                                <input type="text" v-model="form.juridica.ruc" 
                                    @input="form.juridica.ruc = form.juridica.ruc.replace(/\D/g, '').slice(0, 11); form.juridica.tipo_doc_empresa = 'RUC'; buscarDocumento(form.juridica, 'ruc', 'tipo_doc_empresa', 'nombre_empresa')"
                                    minlength="11" maxlength="11"
                                    class="w-full border-gray-300 rounded-md shadow-sm" required />
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
                                    <!-- VALIDACIÓN DNI -->
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
                                <input type="text" v-model="form.juridica.partida" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                            
                            <!-- NUEVO BUSCADOR DE PROVINCIA REGISTRAL -->
                            <div>
                                <label class="block text-gray-700">Provincia (Oficina Registral):</label>
                                <div class="relative">
                                    <input type="text" 
                                        v-model="form.juridica.provincia_registral" 
                                        @focus="mostrarDropdownProvincia = true"
                                        @blur="validarProvincia"
                                        class="w-full border-gray-300 rounded-md shadow-sm pr-10 uppercase" 
                                        placeholder="Seleccione..." required autocomplete="off" />
                                    
                                    <div class="absolute right-3 top-3 pointer-events-none text-gray-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>

                                    <ul v-if="mostrarDropdownProvincia" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-48 overflow-y-auto rounded-md shadow-lg">
                                        <li v-for="prov in provinciasFiltradas" :key="prov"
                                            @mousedown.prevent="seleccionarProvincia(prov)"
                                            class="px-4 py-2 hover:bg-blue-600 hover:text-white cursor-pointer uppercase">
                                            {{ prov }}
                                        </li>
                                        <li v-if="provinciasFiltradas.length === 0" class="px-4 py-2 text-gray-500">
                                            No se encontraron resultados
                                        </li>
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
                                    <!-- VALIDACIÓN DNI -->
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
                            </select>
                        </div>

                        <div v-if="form.natural.estado_civil === 'CASADO'" class="grid grid-cols-2 gap-4 mb-4 bg-gray-50 p-4 rounded-md">
                            <div>
                                <label class="block text-gray-700">Documento del Cónyuge:</label>
                                <div class="flex gap-2">
                                    <select v-model="form.natural.tipo_doc_conyuge" @change="limpiarDocumento(form.natural, 'dni_conyuge', 'tipo_doc_conyuge')" class="w-1/3 border-gray-300 rounded-md shadow-sm" required>
                                        <option value="DNI">DNI</option>
                                        <option value="C.E.">C.E.</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                        <option value="RUC">RUC</option>
                                    </select>
                                    <!-- VALIDACIÓN DNI -->
                                    <input type="text" v-model="form.natural.dni_conyuge" 
                                        @input="limpiarDocumento(form.natural, 'dni_conyuge', 'tipo_doc_conyuge'); buscarDocumento(form.natural, 'dni_conyuge', 'tipo_doc_conyuge', 'nombre_conyuge')"
                                        :minlength="form.natural.tipo_doc_conyuge === 'DNI' ? 8 : (form.natural.tipo_doc_conyuge === 'RUC' ? 11 : null)" 
                                        :maxlength="form.natural.tipo_doc_conyuge === 'DNI' ? 8 : (form.natural.tipo_doc_conyuge === 'RUC' ? 11 : null)" 
                                        class="w-2/3 border-gray-300 rounded-md shadow-sm" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700">Nombre Completo del Cónyuge:</label>
                                <input type="text" v-model="form.natural.nombre_conyuge" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700">Domicilio:</label>
                            <input type="text" v-model="form.natural.domicilio" class="w-full border-gray-300 rounded-md shadow-sm" required />
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
                                        <!-- VALIDACIÓN DNI -->
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
                                        <!-- VALIDACIÓN DNI -->
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

                        <div class="mb-4">
                            <label class="block text-gray-700">Domicilio Principal:</label>
                            <input type="text" v-model="form.copropiedad.domicilio" class="w-full border-gray-300 rounded-md shadow-sm" required />
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
                        
                        <!-- DESPLEGABLE BUSCADOR PARA MODELO -->
                        <div class="relative">
                            <label class="block text-gray-700">Modelo:</label>
                            
                            <!-- Input que parece un select -->
                            <input type="text" 
                                v-model="form.vehiculo.modelo" 
                                @focus="mostrarDropdown = true"
                                @blur="validarModelo"
                                class="w-full border-gray-300 rounded-md shadow-sm pr-10 uppercase" 
                                placeholder="Buscar o seleccionar..." required autocomplete="off" />
                            
                            <!-- Ícono de flecha (diseño simulado de select) -->
                            <div class="absolute right-3 top-9 pointer-events-none text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <!-- Lista flotante con los resultados -->
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
                            <label class="block text-gray-700">N° de Serie / Chasis:</label>
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
import { ref, computed } from 'vue';

const fechaHoy = new Date().toISOString().split('T')[0];

// --- LÓGICA DEL BUSCADOR DE MODELOS ---
const mostrarDropdown = ref(false);

const listaModelos = [
    '_____________', 'FORTUNER', 'AGYA', 'HILUX', 'YARIS' , 'YARIS CROSS', 'COROLLA', 'COROLLA CROSS', '4RUNNER', 'RUSH', 'ETIOS', 'AVENSIS', 'AURIS', 'CAMRY', 'PRIUS', '86', 'AVANZA', 'FJ CRUISER', 'PRADO', 'LC 200', '4RUNNER', 'DUTRO', 'FC', 'FT', 'FG', 'GH', 'FM', 'C-HR', 'HIACE'
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
// --------------------------------------

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
// ---------------------------------------------------

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
        domicilio: ''
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

// FUNCIÓN: Filtra en tiempo real si el documento elegido es "DNI" o "RUC"
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
        
        // 1. Determinamos el nombre del cliente según el tipo que seleccionó
        let nombreCliente = 'CLIENTE';
        
        if (form.tipo_cliente === 'Natural') {
            nombreCliente = form.natural.nombre;
        } else if (form.tipo_cliente === 'Juridica') {
            nombreCliente = form.juridica.nombre_empresa;
        } else if (form.tipo_cliente === 'Copropiedad') {
            // Si es copropiedad, toma el nombre del primer dueño. Si hay más, le agrega "_Y_OTROS"
            nombreCliente = form.copropiedad.lista[0].nombre;
            if (form.copropiedad.lista.length > 1) {
                nombreCliente += "_Y_OTROS";
            }
        }

        // 2. Limpiamos el nombre: cambiamos los espacios por guiones bajos y lo pasamos a mayúsculas
        // Ejemplo: "DIEGO MARCELO" se convierte en "DIEGO_MARCELO"
        const nombreLimpio = nombreCliente.trim().replace(/\s+/g, '_').toUpperCase();

        // 3. Creamos el archivo temporal (blob)
        const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        
        // 4. Creamos un enlace (<a>) invisible en la pantalla
        const link = document.createElement('a');
        link.href = url;
        
        // 5. Armamos tu nombre de archivo personalizado
        link.setAttribute('download', `CARTAS_PODER_${nombreLimpio}.pdf`); 
        
        // 6. Simulamos el clic para que inicie la descarga directa y limpiamos
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // (Opcional) Esta línea además abre el PDF en una pestaña nueva para visualizarlo.
        // Si SOLO quieres que se descargue, puedes borrarla.
        window.open(url, '_blank');

        // 7. LIMPIAR EL FORMULARIO AUTOMÁTICAMENTE
        form.reset();

    } catch (error) {
        console.error("Hubo un error al generar el PDF:", error);
        alert("Ocurrió un error. Por favor, revisa que todos los campos obligatorios estén llenos y los DNI tengan 8 dígitos.");
    }
};

const buscarDocumento = async (entidad, campoDoc, campoTipo, campoNombre) => {
    const tipo = entidad[campoTipo]; // Ej: 'DNI' o 'RUC'
    const doc = entidad[campoDoc];   // Ej: '12345678'

    // 1. Si es DNI y tiene 8 dígitos
    if (tipo === 'DNI' && doc && doc.length === 8) {
        try {
            const respuesta = await axios.get(`/consultar-dni/${doc}`);
            if (respuesta.data.success) {
                const datos = respuesta.data.data;
                entidad[campoNombre] = `${datos.nombres} ${datos.apellido_paterno} ${datos.apellido_materno}`;
            }
        } catch (error) { console.error("Error al buscar DNI"); }
    }
    // 2. Si es RUC y tiene 11 dígitos
    else if (tipo === 'RUC' && doc && doc.length === 11) {
        try {
            const respuesta = await axios.get(`/consultar-ruc/${doc}`);
            if (respuesta.data.success) {
                entidad[campoNombre] = respuesta.data.data.nombre_o_razon_social;
            }
        } catch (error) { console.error("Error al buscar RUC"); }
    }
};
</script>