<?php

    class Libro {

        public $id;
        public $titulo;
        public $autor;
        public $anio;
        public $genero;
        public $editorial;
        public $paginas;

        public function __construct($id, $titulo, $autor, $anio, $genero, $editorial = "", $paginas = 0){
            $this->id = $id;
            $this->titulo = $titulo;
            $this->autor = $autor;
            $this->anio = $anio;
            $this->genero = $genero;
            $this->editorial = $editorial;
            $this->paginas = $paginas;
        }

        public function mostrarLibro(){
            return "<p>Título: $this->titulo</p>";
        }

    }

?>