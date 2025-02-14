function registrar() {
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  console.log(username, password);

  // Send data to Python script using AJAX
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/insert_alumno.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    //if the sql operation was successful, redirect to ./user.html
    if (xhr.readyState == 4 && xhr.status == 200) {
      if (xhr.responseText.trim() != "El usuario ya existe") {
        alert(
          "Creación de cuenta exitosa para: " +
            username +
            ", haga click para continuar"
        );
        localStorage.setItem("id_alumno", xhr.responseText.trim());
        //redirect to ./user.html
        window.location.href = "./lista_cuentos.html";
      } else {
        alert(xhr.responseText);
      }
    }
  };

  const data =
    "nombre=" +
    encodeURIComponent(username) +
    "&contrasena=" +
    encodeURIComponent(password);
  xhr.send(data);

  return false;
}

function iniciarSesion() {
  const username = document.getElementById("iniciarUsuario").value;
  const password = document.getElementById("iniciarPass").value;
  console.log(username, password);

  // You can add validation here if needed

  // Send data to Python script using AJAX
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/login_alumno.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      if (datos.length > 0) {
        alert("¡Bienvenido: " + username + "! Haga click para continuar");
        localStorage.setItem("id_alumno", datos[0].id_alumno);
        window.location.href = "./lista_cuentos.html";
      } else {
        alert("ERROR: Usuario o contraseña incorrectos");
      }
    }
  };

  const data =
    "nombre=" +
    encodeURIComponent(username) +
    "&contrasena=" +
    encodeURIComponent(password);
  xhr.send(data);

  return false;
}

function showIniciarSesion() {
  document.getElementById("inicio").style.display = "block";
  document.getElementById("registro").style.display = "none";
}

function showRegistrarse() {
  document.getElementById("inicio").style.display = "none";
  document.getElementById("registro").style.display = "block";
}

function getDatosAlumno() {
  const id_alumno = localStorage.getItem("id_alumno");
  console.log(id_alumno);
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_alumno.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      console.log(xhr.responseText);
      const html = `
        <label for="nombre">Nombre: </label>
         <input id="nombre" type="text" value="${datos[0].nombre}" disabled>  
        <label for="contrasena">Contraseña: </label>
         <input id="contrasena" type="text" value="${datos[0].contrasena}" disabled>
        `;
      document.getElementById("datos").innerHTML = html;
    }
  };
  const data = "id_alumno=" + encodeURIComponent(id_alumno);
  xhr.send(data);
}

function getDatosAlumnoEditar() {
  const id_alumno = localStorage.getItem("id_alumno");
  console.log(id_alumno);
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_alumno.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      console.log(xhr.responseText);
      const html = `
        <label for="nombre">Nombre: </label>
         <input id="nombre" type="text" value="${datos[0].nombre}" required>
        <label for="contrasena">Contraseña: </label>
         <input id="contrasena" type="text" value="${datos[0].contrasena}" required>
        `;
      document.getElementById("datos").innerHTML = html;
    }
  };
  const data = "id_alumno=" + encodeURIComponent(id_alumno);
  xhr.send(data);
}

function editarAlumno() {
  const id_alumno = localStorage.getItem("id_alumno");
  const nombre = document.getElementById("nombre").value;
  const contrasena = document.getElementById("contrasena").value;
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/editar_alumno.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        // Successful response
        if (
          xhr.responseText.trim() ==
          "Tus datos han sido actualizados correctamente"
        ) {
          console.log(xhr.responseText);
          alert(xhr.responseText.trim());
          window.location.href = "./user.html";
          return false;
        } else {
          // Handle specific error messages from the server
          alert("Error: " + xhr.responseText);
        }
      } else {
        // Handle network or server errors
        alert("Error al actualizar el alumno");
      }
    }
  };

  const data =
    "id_alumno=" +
    encodeURIComponent(id_alumno) +
    "&nombre=" +
    encodeURIComponent(nombre) +
    "&contrasena=" +
    encodeURIComponent(contrasena);
  xhr.send(data);
  return false;
}

function eliminarAlumno() {
  const id_alumno = localStorage.getItem("id_alumno");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/eliminar_alumno.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        // Successful response
        if (
          xhr.responseText.trim() == "Tu cuenta ha sido eliminada correctamente"
        ) {
          console.log(xhr.responseText);
          alert(xhr.responseText.trim());
          localStorage.removeItem("id_alumno");
          window.location.href = "./index.html";
          return false;
        } else {
          // Handle specific error messages from the server
          alert("Error: " + xhr.responseText);
        }
      } else {
        // Handle network or server errors
        alert("Error al eliminar el alumno");
      }
    }
  };

  const data = "id_alumno=" + encodeURIComponent(id_alumno);
  xhr.send(data);
  return false;
}

function getCuentosAlumno() {
  const id_alumno = localStorage.getItem("id_alumno");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_cuentos.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      let html = "";
      if (datos.length > 0) {
        datos.forEach((cuento) => {
          html += `
          <li><button class="btn-lista-cuento" onclick="redirectToVistaCuento(${cuento.id_cuento})">${cuento.nombre}</button></li>
          `;
        });
      } else {
        html = "no hay cuentos disponibles";
      }
      document.getElementById("cuentos-alumno").innerHTML = html;
    }
  };
  const data = "id_alumno=" + encodeURIComponent(id_alumno);
  xhr.send(data);
}

function redirectToVistaCuento(id_cuento) {
  localStorage.setItem("id_cuento", id_cuento);
  window.location.href = "./vista_cuento.html";
}

function getAportacionesCuento() {
  const id_cuento = localStorage.getItem("id_cuento");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_aportaciones.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      let html = "";
      if (datos.length > 0) {
        datos.forEach((aportacion) => {
          html += `<li style="display: inline">`;
          if (aportacion.fk_alumno == localStorage.getItem("id_alumno")) {
            html += `<div style="font-size: 30px; font-weight: bold;"><b>Tu aportación: </b></div>`;
          } else {
            html += `<div style="font-size: 20px;">Alumno:  ${aportacion.alumno}</div>`;
          }
          html += `
            <div class="aportaciones-cuento">${aportacion.contenido}</div>
          </li>
          `;
        });
      } else {
        html = "no hay aportaciones en este cuento";
      }
      document.getElementById("aportaciones-cuento").innerHTML = html;
    }
  };
  const data = "id_cuento=" + encodeURIComponent(id_cuento);
  xhr.send(data);
}

function getVistaDatosCuento() {
  //get nombre and descripcion from cuento
  const id_cuento = localStorage.getItem("id_cuento");
  const id_alumno = localStorage.getItem("id_alumno");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_vista_cuento.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      let html = "";
      // create a button to either remove the cuento or remove the apostacion
      if (datos[0].fk_owner == id_alumno) {
        html += `
          <button class="delete" onclick="document.getElementById('popup-delete-cuento').style.display = 'block'">Eliminar tu cuento</button>
          <button onclick="window.location.href = './editar_cuento.html'">Edita los datos de tu cuento</button>
        `;
      } else {
        html += `
          <button onclick="document.getElementById('popup-delete-aportacion').style.display = 'block'">Abandonar cuento</button>
        `;
      }
      html += `
            <button onclick="window.location.href = './editar_aportacion.html'">
      Editar mi aportacion
    </button>
    <button type="button" onclick="window.location.href = './historial.html'">
      Ver historial de cambios
    </button>
`;
      html += `
          <h1>Cuento: ${datos[0].nombre}</h1>
          <h3>Descripción: ${datos[0].descripcion}</h3>
          <h3>Código del cuento: ${datos[0].id_cuento}</h3>
        `;
      if (datos[0].fk_owner == localStorage.getItem("id_alumno")) {
        html += `
          <h4><i>Este cuento ha sido creado por ti<i></h4>
        `;
      }
      document.getElementById("vista-datos-cuento").innerHTML = html;
    }
  };
  const data = "id_cuento=" + encodeURIComponent(id_cuento);
  xhr.send(data);
}

function getVistaDatosCuentoEditar() {
  const id_cuento = localStorage.getItem("id_cuento");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_vista_cuento.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      let html = "";
      html += `
         <label for="nombre_cuento_editar">Nombre: </label> 
          <input id="nombre_cuento_editar" type="text" value="${datos[0].nombre}" required>
          <br>
          <label for="descripcion_cuento_editar">Descripción: </label>
          <input id="descripcion_cuento_editar" type="text" value="${datos[0].descripcion}" required>
        `;
      document.getElementById("datos-cuento-editar").innerHTML = html;
    }
  };
  const data = "id_cuento=" + encodeURIComponent(id_cuento);
  xhr.send(data);
}

function editarCuento() {
  const id_cuento = localStorage.getItem("id_cuento");
  const nombre = document.getElementById("nombre_cuento_editar").value;
  const descripcion = document.getElementById(
    "descripcion_cuento_editar"
  ).value;
  xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/editar_cuento.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      if (
        xhr.responseText.trim() ==
        "Los datos de tu cuento se han actualizado correctamente"
      ) {
        console.log(xhr.responseText);
        alert(xhr.responseText.trim());
        window.location.href = "./vista_cuento.html";
        return false;
      } else {
        alert("Error al actualizar el cuento");
      }
    }
  };
  const data =
    "id_cuento=" +
    encodeURIComponent(id_cuento) +
    "&nombre_cuento=" +
    encodeURIComponent(nombre) +
    "&descripcion_cuento=" +
    encodeURIComponent(descripcion);
  xhr.send(data);
  return false;
}

function unirseCuento() {
  const id_alumno = localStorage.getItem("id_alumno");
  const id_cuento = document.getElementById("id_cuento_unirse").value;

  if (id_cuento == "" || id_cuento == null || isNaN(id_cuento)) {
    alert("Ingrese un id de cuento válido");
    return false;
  }

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/unirse.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  console.log(xhr);

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        // Successful response
        if (
          xhr.responseText.trim() ==
          "El alumno se ha unido al cuento correctamente"
        ) {
          console.log(xhr.responseText);
          localStorage.setItem("id_cuento", id_cuento);
          alert(
            "Se ha unido al cuento exitosamente. Haga click para continuar"
          );
          window.location.href = "./vista_cuento.html";
          return false;
        } else {
          // Handle specific error messages from the server
          alert("Error: " + xhr.responseText);
        }
      } else {
        // Handle network or server errors
        console.log(xhr);
        alert(
          "Error al unirse al cuento. Verificar que el código sea correcto y que no se haya unido anteriormente al cuento"
        );
      }
    }
  };

  const data =
    "id_cuento=" +
    encodeURIComponent(id_cuento) +
    "&id_alumno=" +
    encodeURIComponent(id_alumno);
  xhr.send(data);
  return false;
}

function crearCuento() {
  const id_alumno = localStorage.getItem("id_alumno");
  const nombre = document.getElementById("nombre_cuento_crear").value;
  const descripcion = document.getElementById("descripcion_cuento_crear").value;

  if (nombre == "" || nombre == null) {
    alert("Ingrese un nombre para el cuento");
    return false;
  }

  if (descripcion == "" || descripcion == null) {
    alert("Ingrese una descripción para el cuento");
    return false;
  }
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/crear_cuento.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      console.log(xhr.responseText);
      // Successful response
      const id_cuento = xhr.responseText.split(" ")[1];
      localStorage.setItem("id_cuento", parseInt(id_cuento));
      alert("Se ha creado el cuento exitosamente. Haga click para continuar");
      window.location.href = "./lista_cuentos.html";
      return false;
    }
  };

  const data =
    "nombre=" +
    encodeURIComponent(nombre) +
    "&descripcion=" +
    encodeURIComponent(descripcion) +
    "&id_alumno=" +
    encodeURIComponent(id_alumno);
  xhr.send(data);
  return false;
}

function getCuentoCompleto() {
  const id_cuento = localStorage.getItem("id_cuento");
  const id_alumno = localStorage.getItem("id_alumno");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_aportaciones.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      let html = "";
      if (datos.length > 0) {
        datos.forEach((aportacion) => {
          html += `
            <p>${aportacion.contenido}</p>
          `;
          if (aportacion.fk_alumno == id_alumno) {
            document.getElementById("aportacion").innerHTML =
              aportacion.contenido;
          }
        });
      } else {
        html = "no hay aportaciones en este cuento";
      }
      document.getElementById("cuento-completo").innerHTML = html;
    }
  };
  const data = "id_cuento=" + encodeURIComponent(id_cuento);
  xhr.send(data);
}

function guardarAportacion() {
  const id_cuento = localStorage.getItem("id_cuento");
  const id_alumno = localStorage.getItem("id_alumno");
  const aportacion = document.getElementById("aportacion").value;
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/editar_aportacion.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      console.log(xhr.responseText);
      window.location.href = "./vista_cuento.html";
      return false;
    }
  };
  const data =
    "id_cuento=" +
    encodeURIComponent(id_cuento) +
    "&id_alumno=" +
    encodeURIComponent(id_alumno) +
    "&aportacion=" +
    encodeURIComponent(aportacion);
  xhr.send(data);
  return false;
}

function historial() {
  const id_cuento = localStorage.getItem("id_cuento");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/get_historial.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const datos = JSON.parse(xhr.responseText);
      let html = "";
      if (datos.length > 0) {
        // insert as rows in a table
        datos.forEach((historial) => {
          html += `
            <tr>
              <td>${historial.fecha_creacion}</td>
              <td>${historial.cambio}</td>
            </tr>
          `;
        });
      } else {
        html = "no hay historial en este cuento";
      }
      document.getElementById("historial-cambios").innerHTML += html;
    }
  };
  const data = "id_cuento=" + encodeURIComponent(id_cuento);
  xhr.send(data);
}

function eliminarCuento() {
  const id_cuento = localStorage.getItem("id_cuento");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/eliminar_cuento.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      console.log(xhr.responseText);
      alert(xhr.responseText.trim());
      window.location.href = "./lista_cuentos.html";
      return false;
    }
  };
  const data = "id_cuento=" + encodeURIComponent(id_cuento);
  xhr.send(data);
  return false;
}

function eliminarAportacion() {
  const id_cuento = localStorage.getItem("id_cuento");
  const id_alumno = localStorage.getItem("id_alumno");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "./php/eliminar_aportacion.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      console.log(xhr.responseText);
      alert(xhr.responseText.trim());
      window.location.href = "./lista_cuentos.html";
      return false;
    }
  };
  const data =
    "id_cuento=" +
    encodeURIComponent(id_cuento) +
    "&id_alumno=" +
    encodeURIComponent(id_alumno);
  xhr.send(data);
  return false;
}

//I'm dumb and I don't know other way to load components, so this takes the class of the body and loads the components depending on the class
function loadComponents() {
  if (document.body.classList.contains("datos-alumno")) {
    getDatosAlumno();
  }
  if (document.body.classList.contains("editar-alumno")) {
    getDatosAlumnoEditar();
  }
  if (document.body.classList.contains("lista-cuentos")) {
    getCuentosAlumno();
  }
  if (document.body.classList.contains("vista-cuento")) {
    getVistaDatosCuento();
    getAportacionesCuento();
  }
  if (document.body.classList.contains("editar-aportacion")) {
    getCuentoCompleto();
  }
  if (document.body.classList.contains("historial")) {
    historial();
  }
  if (document.body.classList.contains("editar-cuento")) {
    getVistaDatosCuentoEditar();
  }
}

loadComponents();
