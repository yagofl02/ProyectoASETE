<?php    
    
    require_once "models/pelicula.php";

    $peliculas = [
        new Pelicula("El editor de libros", 2016, "Michael Grandage", "Colin Firth, Jude Law, Nicole Kidman", "Biografía"),
        new Pelicula("Un amor entre dos mundos", 2012, "Juan Diego Solanas", "Jim Sturgess, Kirsten Dunst, Timothy Spall", "Ciencia ficción"),
        new Pelicula("Una cuestión de tiempo", 2013, "Richard Curtis", "Domhnall Gleeson, Rachel McAdams, Bill Nighy", "Romance"),
        new Pelicula("El indomable Will Hunting", 1997, "Gus Van Sant", "Matt Damon, Robin Williams, Ben Affleck", "Drama"),
        new Pelicula("Descubriendo a Forrester", 2000, "Gus Van Sant", "Sean Connery, Rob Brown, F. Murray Abraham, Anna Paquin", "Drama"),
        new Pelicula("El club de los poetas muertos", 1989, "Peter Weir", "Robin Williams, Robert Sean Leonard, Ethan Hawke, Josh Charles", "Drama"),
        new Pelicula("Gattaca", 1997, "Andrew Niccol", "Ethan Hawke, Uma Thurman, Jude Law, Loren Dean", "Ciencia ficción"),
        new Pelicula("In Time", 2011, "Andrew Niccol", "Justin Timberlake, Amanda Seyfried, Vincent Kartheiser", "Ciencia ficción"),
        new Pelicula("Una mente maravillosa", 2001, "Ron Howard", "Russell Crowe, Ed Harris, Jennifer Connelly", "Biografía"),
        new Pelicula("Big Fish", 2003, "Tim Burton", "Ewan McGregor, Albert Finney, Billy Crudup, Jessica Lange", "Drama"),
        new Pelicula("El club de la lucha", 1999, "David Fincher", "Edward Norton, Brad Pitt, Helena Bonham Carter", "Thriller"),
        new Pelicula("Eduardo Manostijeras", 1990, "Tim Burton", "Johnny Depp, Winona Ryder, Dianne Wiest", "Fantasía"),
        new Serie("Hijos de la Anarquía", 2008, "Kurt Sutter", "Charlie Hunnam, Ron Perlman, Katey Sagal", "Drama", 8),
        new Serie("Peaky Blinders", 2013, "Steven Knight", "Cillian Murphy, Paul Anderson, Helen McCrory", "Drama", 5),
        new Corto("Frankenweenie", 1984, "Tim Burton", "Shelley Duvall, Daniel Stern, Barrett Oliver", "Ciencia Ficción", 20)
    ];
?>