<!-- Footer Start -->
    <div class="container-fluid bg-dark text-light footer mt-5 pt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-light mb-4">Address</h5>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>123 Street, New York, USA</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+012 345 67890</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>info@example.com</p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-outline-light btn-social rounded-circle" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-outline-light btn-social rounded-circle" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-light btn-social rounded-circle" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-outline-light btn-social rounded-circle" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-light mb-4">Services</h5>
                    @if(isset($specialties) && $specialties->count() > 0)
                        @foreach($specialties->take(5) as $specialty)
                            <a class="btn btn-link" href="">{{ $specialty->name }}</a>
                        @endforeach
                    @else
                        <!-- Fallback jika tidak ada specialty -->
                        <a class="btn btn-link" href="">Cardiology</a>
                        <a class="btn btn-link" href="">Pulmonary</a>
                        <a class="btn btn-link" href="">Neurology</a>
                        <a class="btn btn-link" href="">Orthopedics</a>
                        <a class="btn btn-link" href="">Laboratory</a>
                    @endif
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-light mb-4">Quick Links</h5>
                    <a class="btn btn-link" href="#about-section">About Us</a>
                    <a class="btn btn-link" href="#contact-section">Contact Us</a>
                    <a class="btn btn-link" href="#service-section">Our Services</a>
                </div>
                <div class="col-lg-3 col-md-6">
                  
                        @auth
                            @if(!auth()->user())
                              <h5 class="text-light mb-4">Newsletter</h5>
                                  <p>Dolor amet sit justo amet elitr clita ipsum elitr est.</p>
                                    <div class="position-relative mx-auto" style="max-width: 400px;"></div>
                                 <input class="form-control border-0 w-100 py-3 ps-4 pe-5" type="text" placeholder="Your email">
                                <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">SignUp</button>
                            @else
                               
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="border-bottom" href="#">Copyright 2025, PT Airlangga Hospitals Tbk.</a>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->