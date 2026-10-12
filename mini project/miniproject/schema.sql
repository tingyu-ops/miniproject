CREATE DATABASE movie_review;

USE movie_review;

CREATE TABLE users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','movie_moderator','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE genres (
    genre_id   INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE movies (
    movie_id     INT AUTO_INCREMENT PRIMARY KEY,
    genre_id     INT NOT NULL,
    title        VARCHAR(255) NOT NULL,
    release_year SMALLINT,
    description  TEXT,
    poster       VARCHAR(500),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_movies_genre FOREIGN KEY (genre_id)
        REFERENCES genres(genre_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE reviews (
    review_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    movie_id      INT NOT NULL,
    rating        TINYINT NOT NULL,
    comment       TEXT NOT NULL,
    review_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    review_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_reviews_user  FOREIGN KEY (user_id)  REFERENCES users(user_id)   ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie_review (user_id, movie_id)
) ENGINE=InnoDB;

CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT NOT NULL,
    movie_id  INT NOT NULL,
    rating    TINYINT NOT NULL,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_ratings_user  FOREIGN KEY (user_id)  REFERENCES users(user_id)   ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ratings_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie_rating (user_id, movie_id)
) ENGINE=InnoDB;

CREATE TABLE watchlist (
    watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    movie_id     INT NOT NULL,
    added_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_watchlist_user  FOREIGN KEY (user_id)  REFERENCES users(user_id)   ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_watchlist_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie_watchlist (user_id, movie_id)
) ENGINE=InnoDB;

CREATE TABLE review_reports (
    report_id   INT AUTO_INCREMENT PRIMARY KEY,
    review_id   INT NOT NULL,
    user_id     INT NOT NULL,
    reason      VARCHAR(255) NOT NULL,
    status      ENUM('open','resolved') NOT NULL DEFAULT 'open',
    report_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_review FOREIGN KEY (review_id) REFERENCES reviews(review_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reports_user   FOREIGN KEY (user_id)   REFERENCES users(user_id)     ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY unique_user_report (review_id, user_id)
) ENGINE=InnoDB;

CREATE INDEX idx_movies_title  ON movies(title);
CREATE INDEX idx_movies_genre  ON movies(genre_id);
CREATE INDEX idx_movies_year   ON movies(release_year);
CREATE INDEX idx_reviews_movie ON reviews(movie_id);
CREATE INDEX idx_reviews_user  ON reviews(user_id);
CREATE INDEX idx_reviews_status ON reviews(review_status);
CREATE INDEX idx_ratings_movie ON ratings(movie_id);
CREATE INDEX idx_watchlist_user ON watchlist(user_id);

INSERT INTO genres (genre_name) VALUES
('Action'),('Adventure'),('Animation'),('Comedy'),('Crime'),('Drama'),
('Fantasy'),('Horror'),('Romance'),('Science Fiction'),('Thriller'),('Mystery');

INSERT INTO users (name, email, password, role) VALUES
('System Admin','admin@moviereview.com','$2y$12$8x6z3n2fNzWLZEP8laYw4u3xkJjzR2Axi8HUJNYoxqwp3UDLi8yZu','admin'),
('Movie Moderator','moderator@moviereview.com','$2y$12$3ckgpr1IV.b1o/Ui.t3j.ehiCYMe6D4AWpT.F.QRVCb5QJ44CaYyS','movie_moderator'),
('Peter User','peter@example.com','$2y$12$ucOt/SCZXsRDyEUtSFlW5eu8LT1gdu0uiy5moFcLExdg1xeTOle7.','user'),
('Jane User','jane@example.com','$2y$12$YUcFlGiOK9mYSlVEVTl5zOP3zIr1vlAdUZuyVVsVhlRz98MgRr6Vi','user'),
('Adam User','adam@example.com','$2y$12$QbcdD.h4aHe.tdDnuoUTTOiYNI2yr9GIqUCDpIdUSDWWqoPt8YdfW','user'),
('Jess User','jess@example.com','$2y$12$z6l/PtUXXaVgI0gUcxvjM.i72pJvy5cakwt.BA3.p/d4XsCZ46DJy','user');

INSERT INTO movies (genre_id, title, release_year, description, poster) VALUES
(1,  'The Dark Knight', 2008, 'A masked vigilante fights crime in Gotham City while facing a dangerous criminal mastermind.', 'dark-knight.jpg'),
(7,  'The Lord of the Rings: The Fellowship of the Ring', 2001, 'A young hobbit begins an extraordinary journey to destroy a powerful ring.', 'lord-of-the-rings.jpg'),
(3,  'Toy Story', 1995, 'A group of toys comes to life when humans are not around.', 'toy-story.jpg'),
(4,  'The Hangover', 2009, 'Three friends try to remember what happened during a wild night in Las Vegas.', 'the-hangover.jpg'),
(5,  'The Godfather', 1972, 'The story of a powerful crime family and its struggle to maintain control.', 'the-godfather.jpg'),
(6,  'Forrest Gump', 1994, 'A kind-hearted man experiences several important moments in American history.', 'forrest-gump.jpg'),
(7,  'Harry Potter and the Philosopher''s Stone', 2001, 'A young boy discovers that he is a wizard and begins his magical education.', 'harry-potter.jpg'),
(8,  'The Conjuring', 2013, 'Paranormal investigators help a family experiencing terrifying supernatural events.', 'the-conjuring.jpg'),
(9,  'The Notebook', 2004, 'A romantic story about two people whose lives remain connected through the years.', 'the-notebook.jpg'),
(10, 'Inception', 2010, 'A skilled thief enters the dreams of others to steal or plant information.', 'inception.jpg');


INSERT INTO ratings (user_id, movie_id, rating) VALUES
(3,1,5),
(4,2,5),
(6,3,4),
(5,10,5),
(5,1,4),
(4,3,4),
(3,6,4),
(3,9,2);

INSERT INTO reviews (user_id, movie_id, rating, comment, review_status) VALUES
(3,1, 5,'Amazing movie with great acting and an excellent story.','approved'),
(4,2, 5,'A fantastic adventure movie with beautiful world building.','approved'),
(5,10,5,'Very interesting story. The dream concept was excellent.','approved'),
(4,3, 4,'A fun and emotional animated movie.','approved'),
(3,6, 4,'A touching movie with a memorable main character.','approved'),
(3,9, 2,'A romantic movie, but the pacing felt slow for me.','pending');

INSERT INTO watchlist (user_id, movie_id) VALUES (4,4),(5,5),(4,2),(3,2),(6,7),(3,10);
