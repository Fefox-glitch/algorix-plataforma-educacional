# Manual de Gestión de Laboratorio - Algorix Lab Manager

## Índice

1. [Introducción](#introducción)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Manual del Administrador](#manual-del-administrador)
4. [Manual del Profesor](#manual-del-profesor)
5. [Configuración de Computadoras Cliente](#configuración-de-computadoras-cliente)
6. [Solución de Problemas](#solución-de-problemas)

---

## Introducción

Algorix Lab Manager es un sistema centralizado para la gestión de laboratorios de computadoras que permite:

- Gestionar 30+ computadoras desde un panel central
- Organizar estudiantes en grupos
- Asignar computadoras a grupos específicos
- Controlar remotamente el encendido, apagado y reinicio de equipos
- Monitorear el estado en tiempo real de todas las computadoras
- Llevar registro de sesiones de uso del laboratorio

### Roles del Sistema

- **Administrador**: Control total del sistema, gestión de laboratorios, computadoras, grupos y profesores
- **Profesor**: Gestión de sus grupos asignados y control de computadoras en sus laboratorios
- **Estudiante**: Visualización de su grupo y computadora asignada

---

## Requisitos del Sistema

### Servidor Central

- PHP 7.4 o superior
- Base de datos Supabase (PostgreSQL)
- Apache/Nginx
- Acceso a red local para comunicación con computadoras

### Computadoras Cliente

- Sistema operativo: Windows/Linux
- Conexión a red local
- Wake-on-LAN habilitado en BIOS
- Puerto 8080 abierto para recibir comandos
- Agente cliente instalado (opcional, para comandos avanzados)

---

## Manual del Administrador

### 1. Acceso al Panel de Administración

1. Ingresar a la URL del sistema
2. Iniciar sesión con credenciales de administrador
3. Acceder al panel de administración desde el menú principal

### 2. Gestión de Laboratorios

#### Crear un Nuevo Laboratorio

1. En el panel de administración, seleccionar **"Laboratorios"**
2. Hacer clic en **"+ Nuevo Laboratorio"**
3. Completar los datos:
   - **Nombre**: Identificador del laboratorio (ej: "Lab A", "Sala 101")
   - **Ubicación**: Ubicación física (ej: "Edificio B, Piso 2")
   - **Capacidad**: Número máximo de computadoras (ej: 30)
4. Hacer clic en **"Guardar"**

#### Editar un Laboratorio

1. Localizar el laboratorio en la lista
2. Hacer clic en el botón **"Editar"**
3. Modificar los campos necesarios
4. Hacer clic en **"Guardar cambios"**

#### Desactivar un Laboratorio

1. Editar el laboratorio
2. Cambiar el estado a **"Inactivo"**
3. Guardar cambios

### 3. Gestión de Computadoras

#### Registrar una Nueva Computadora

1. Seleccionar **"Computadoras"** en el menú
2. Hacer clic en **"+ Nueva Computadora"**
3. Completar los datos:
   - **Nombre**: Identificador único (ej: "PC-01", "WS-15")
   - **Laboratorio**: Seleccionar el laboratorio al que pertenece
   - **Dirección IP**: IP local de la computadora (ej: 192.168.1.100)
   - **Dirección MAC**: MAC address para Wake-on-LAN (ej: AA:BB:CC:DD:EE:FF)
   - **Especificaciones**: CPU, RAM, Disco (opcional)
4. Hacer clic en **"Registrar"**

#### Editar Información de Computadora

1. Localizar la computadora en la lista
2. Hacer clic en **"Editar"**
3. Modificar los campos necesarios
4. Guardar cambios

#### Estados de Computadora

- **Online**: Computadora encendida y respondiendo
- **Offline**: Computadora apagada o sin conexión
- **Maintenance**: En mantenimiento (configurado manualmente)
- **Error**: Error detectado en la computadora

### 4. Gestión de Grupos

#### Crear un Grupo de Estudiantes

1. Seleccionar **"Grupos"** en el menú
2. Hacer clic en **"+ Nuevo Grupo"**
3. Completar los datos:
   - **Nombre**: Nombre descriptivo (ej: "Grupo A - Turno Mañana")
   - **Profesor**: Asignar un profesor responsable
   - **Laboratorio**: Seleccionar laboratorio asignado
   - **Horario**: Días y horas de clase (formato JSON)
4. Hacer clic en **"Crear"**

#### Agregar Estudiantes a un Grupo

1. Abrir los detalles del grupo
2. Hacer clic en **"+ Agregar Estudiante"**
3. Buscar y seleccionar estudiantes
4. Confirmar la asignación

#### Asignar Computadoras a un Grupo

1. Abrir los detalles del grupo
2. Hacer clic en **"Asignar Computadoras"**
3. Seleccionar las computadoras disponibles
4. Configurar fecha de expiración (opcional)
5. Guardar la asignación

### 5. Control de Acciones Masivas

#### Encender Todas las Computadoras de un Laboratorio

1. Ir a la sección **"Acciones"**
2. Seleccionar el laboratorio en el menú desplegable
3. Hacer clic en **"⚡ Encender Todas"**
4. Confirmar la acción
5. El sistema enviará comandos Wake-on-LAN a todas las computadoras

#### Apagar Todas las Computadoras

1. Seleccionar el laboratorio
2. Hacer clic en **"🔴 Apagar Todas"**
3. Confirmar la acción
4. El sistema enviará comandos de apagado a todas las computadoras

#### Reiniciar Computadoras

1. Seleccionar el laboratorio
2. Hacer clic en **"🔄 Reiniciar Todas"**
3. Confirmar la acción

### 6. Monitoreo de Sesiones

#### Ver Sesiones Activas

1. En el panel principal, la sección **"Sesiones Activas"** muestra:
   - Laboratorio en uso
   - Grupo asignado
   - Profesor a cargo
   - Hora de inicio

#### Ver Historial de Sesiones

1. Ir a la sección **"Sesiones"**
2. Revisar el historial completo con:
   - Fecha y hora de inicio/fin
   - Duración de la sesión
   - Notas del profesor

### 7. Gestión de Profesores

#### Asignar Rol de Profesor

1. Localizar el usuario en el sistema
2. Cambiar su rol a **"Profesor"**
3. Asignar grupos y laboratorios correspondientes

#### Ver Grupos de un Profesor

1. Ir a la sección **"Profesores"**
2. Seleccionar un profesor
3. Ver los grupos asignados y estadísticas

---

## Manual del Profesor

### 1. Acceso al Panel de Profesor

1. Iniciar sesión con credenciales de profesor
2. Acceder al panel de profesor desde el menú principal

### 2. Gestión de Grupos

#### Ver Mis Grupos

1. En **"Mis Grupos"** se muestran todos los grupos asignados
2. Hacer clic en un grupo para ver detalles:
   - Lista de estudiantes
   - Computadoras asignadas
   - Horario de clases

#### Ver Miembros del Grupo

1. Seleccionar un grupo
2. La tabla muestra:
   - Nombre del estudiante
   - Email
   - Computadora asignada
   - Fecha de ingreso al grupo

### 3. Control de Laboratorio

#### Iniciar una Sesión de Laboratorio

1. Ir a **"Control de Laboratorio"**
2. Seleccionar el laboratorio asignado
3. Hacer clic en **"Iniciar Sesión"**
4. El sistema registrará la sesión actual

#### Encender Computadoras

**Opción 1: Encender Computadoras Individuales**
1. Seleccionar las computadoras deseadas (checkbox)
2. Hacer clic en **"⚡ Encender Seleccionadas"**

**Opción 2: Encender por Grupo**
1. El sistema puede encender automáticamente todas las computadoras del grupo
2. Hacer clic en **"Encender Grupo Completo"**

#### Controlar Computadoras Durante la Clase

- **Reiniciar**: Reinicia las computadoras seleccionadas
- **Bloquear**: Bloquea la pantalla de las computadoras (requiere agente cliente)
- **Apagar**: Apaga las computadoras seleccionadas

#### Monitorear Estado de Computadoras

El panel muestra en tiempo real:
- Estado (Online/Offline)
- Última conexión
- Estudiante asignado
- IP de la computadora

### 4. Asignaciones de Computadoras

#### Asignar Computadora a un Estudiante

1. Ir a **"Asignaciones"**
2. Hacer clic en **"+ Nueva Asignación"**
3. Seleccionar:
   - Computadora disponible
   - Estudiante del grupo
   - Fecha de expiración (opcional)
4. Agregar notas si es necesario
5. Confirmar la asignación

#### Ver Todas las Asignaciones

1. La tabla de asignaciones muestra:
   - Computadora asignada
   - Estudiante o grupo
   - Fecha de asignación
   - Fecha de expiración
2. Filtrar por grupo usando el selector

#### Eliminar una Asignación

1. Localizar la asignación en la tabla
2. Hacer clic en **"Eliminar"**
3. Confirmar la acción

### 5. Finalizar Sesión de Laboratorio

1. Al terminar la clase, ir a **"Control de Laboratorio"**
2. Hacer clic en **"Finalizar Sesión"**
3. Opcionalmente agregar notas sobre la sesión
4. El sistema registrará la hora de fin y duración

### 6. Ver Historial de Sesiones

1. Ir a **"Mis Sesiones"**
2. Revisar el historial con:
   - Fecha de la sesión
   - Laboratorio utilizado
   - Grupo asignado
   - Duración
   - Notas

---

## Configuración de Computadoras Cliente

### 1. Habilitar Wake-on-LAN (WOL)

#### En BIOS/UEFI

1. Reiniciar la computadora y entrar a BIOS (generalmente con F2, F10, o DEL)
2. Buscar la opción **"Wake-on-LAN"**, **"Power On by PCI/PCIe"** o similar
3. Habilitar la opción
4. Guardar cambios y salir

#### En Windows

1. Abrir **"Administrador de dispositivos"**
2. Expandir **"Adaptadores de red"**
3. Clic derecho en el adaptador de red → **"Propiedades"**
4. Ir a la pestaña **"Administración de energía"**
5. Marcar:
   - ✅ Permitir que este dispositivo reactive el equipo
   - ✅ Solo permitir un paquete mágico para reactivar el equipo
6. Ir a la pestaña **"Opciones avanzadas"**
7. Buscar **"Wake on Magic Packet"** y establecer en **"Habilitado"**
8. Hacer clic en **"Aceptar"**

#### En Linux

```bash
sudo ethtool -s eth0 wol g
```

Para hacerlo permanente, agregar a `/etc/network/interfaces`:
```
post-up /usr/sbin/ethtool -s eth0 wol g
```

### 2. Configurar IP Estática (Recomendado)

#### Windows

1. Panel de Control → Red e Internet → Centro de redes
2. Cambiar configuración del adaptador
3. Clic derecho en el adaptador → Propiedades
4. Seleccionar **"Protocolo de Internet versión 4 (TCP/IPv4)"**
5. Configurar IP estática:
   - IP: 192.168.1.X (según configuración de red)
   - Máscara: 255.255.255.0
   - Puerta de enlace: 192.168.1.1

#### Linux

Editar `/etc/network/interfaces`:
```
auto eth0
iface eth0 inet static
    address 192.168.1.X
    netmask 255.255.255.0
    gateway 192.168.1.1
```

### 3. Obtener Dirección MAC

#### Windows

```cmd
ipconfig /all
```
Buscar **"Dirección física"**

#### Linux

```bash
ip link show
```
o
```bash
ifconfig
```

### 4. Instalar Agente Cliente (Opcional)

Para funcionalidades avanzadas como bloqueo remoto, instalar el agente:

1. Descargar el agente desde el servidor
2. Ejecutar el instalador
3. Configurar el puerto (por defecto: 8080)
4. El agente se ejecutará como servicio

---

## Solución de Problemas

### Wake-on-LAN No Funciona

**Problema**: Las computadoras no encienden con el comando remoto

**Soluciones**:
1. Verificar que WOL esté habilitado en BIOS
2. Comprobar configuración de red en Windows/Linux
3. Verificar que la dirección MAC sea correcta
4. Asegurarse de que las computadoras estén conectadas por cable (no WiFi)
5. Comprobar que el switch de red soporte WOL

### Computadora Muestra Estado "Offline" Pero Está Encendida

**Soluciones**:
1. Verificar la dirección IP de la computadora
2. Comprobar firewall (debe permitir ping y puerto 8080)
3. Verificar conexión de red
4. Actualizar manualmente el estado en el panel

### Comando de Apagado No Funciona

**Soluciones**:
1. Verificar que el agente cliente esté instalado
2. Comprobar que el puerto 8080 esté abierto
3. Verificar permisos del agente
4. Revisar logs del agente en la computadora cliente

### No Puedo Asignar Computadoras a un Grupo

**Soluciones**:
1. Verificar que el grupo tenga un laboratorio asignado
2. Comprobar que las computadoras pertenezcan al mismo laboratorio
3. Verificar permisos de usuario

### Error al Crear Sesión de Laboratorio

**Soluciones**:
1. Verificar que el grupo tenga un laboratorio asignado
2. Comprobar que no haya otra sesión activa en el mismo laboratorio
3. Verificar conexión con la base de datos

---

## Mejores Prácticas

### Para Administradores

1. **Nomenclatura Consistente**: Usar nombres descriptivos y consistentes para laboratorios y computadoras
2. **Revisión Regular**: Revisar el estado de las computadoras semanalmente
3. **Mantenimiento Preventivo**: Marcar computadoras en mantenimiento antes de hacer cambios
4. **Backup de Datos**: Realizar respaldo regular de la base de datos
5. **Actualizar Información**: Mantener actualizada la información de IP y MAC

### Para Profesores

1. **Iniciar Sesión**: Siempre iniciar una sesión al comenzar la clase
2. **Verificar Estado**: Revisar el estado de las computadoras antes de la clase
3. **Apagar al Terminar**: Apagar las computadoras al finalizar la sesión
4. **Reportar Problemas**: Notificar al administrador sobre computadoras con problemas
5. **Usar Notas**: Agregar notas útiles en las sesiones para referencia futura

---

## Contacto y Soporte

Para soporte técnico o reportar problemas:
- Email: soporte@algorix.edu
- Portal de tickets: [URL del sistema de tickets]
- Teléfono: [Número de contacto]

---

## Notas Importantes

- Este sistema requiere que todas las computadoras estén en la misma red local
- Wake-on-LAN funciona solo con conexiones por cable Ethernet
- Los comandos de apagado/reinicio requieren el agente cliente instalado
- Se recomienda usar IPs estáticas para facilitar la gestión
- El sistema registra todas las acciones para auditoría

---

**Versión del Manual**: 1.0
**Última Actualización**: 2025-10-19
**Sistema**: Algorix Lab Manager
