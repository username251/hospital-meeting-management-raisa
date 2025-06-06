<div class="container-fluid header bg-primary p-0 mb-5">
    <div class="row g-0 align-items-center flex-column-reverse flex-lg-row">
        <div class="col-lg-6 p-5 wow fadeIn" data-wow-delay="0.1s">
            <h1 class="display-4 text-white mb-5">Good Health Is The Root Of All Heppiness</h1>
            <div class="row g-4">
                <div class="col-sm-4">
                    <div class="border-start border-light ps-4">
                        <h2 class="text-white mb-1" data-toggle="counter-up">2831</h2>
                        <p class="text-light mb-0">Expert Doctors</p>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="border-start border-light ps-4">
                        <h2 class="text-white mb-1" data-toggle="counter-up">1234</h2>
                        <p class="text-light mb-0">Medical Stuff</p>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="border-start border-light ps-4">
                        <h2 class="text-white mb-1" data-toggle="counter-up">4532</h2>
                        <p class="text-light mb-0">Total Patients</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
            <div class="owl-carousel header-carousel">
                <div class="owl-carousel-item">
                    <img class="img-fluid" src="img/carousel-1.jpg" alt="">
                </div>
                <div class="owl-carousel-item">
                    <img class="img-fluid" src="img/carousel-2.jpg" alt="">
                </div>
                <div class="owl-carousel-item">
                    <img class="img-fluid" src="img/carousel-3.jpg" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-xxl py-5" id="about-section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column">
                    <img class="img-fluid rounded w-75 align-self-end" src="img/about-1.jpg" alt="">
                    <img class="img-fluid rounded w-50 bg-white pt-3 pe-3" src="img/about-2.jpg" alt="" style="margin-top: -25%;">
                </div>
            </div>
            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                <p class="d-inline-block border rounded-pill py-1 px-4">About Us</p>
                <h1 class="mb-4">Why You Should Trust Us? Get Know About Us!</h1>
                <p>Tempor erat elitr rebum clita dolor diam ipsum sit diam amet diam et eos. Clita erat ipsum et lorem et sit, sed stet lorem sit clita duo justo magna dolore erat amet</p>
                <p class="mb-4">Stet no et lorem dolor et diam, amet duo ut dolore vero eos. No stet est diam rebum amet diam ipsum. Clita clita labore, dolor duo nonumy clita sit at, sed sit sanctus dolor eos.</p>
                <p><i class="far fa-check-circle text-primary me-3"></i>Quality health care</p>
                <p><i class="far fa-check-circle text-primary me-3"></i>Only Qualified Doctors</p>
                <p><i class="far fa-check-circle text-primary me-3"></i>Medical Research to ensure the best</p>
                <a class="btn btn-primary rounded-pill py-3 px-5 mt-3" href="">Read More</a>
            </div>
        </div>
    </div>
</div>
<!-- Service Start -->
<div class="container-xxl py-5" id="service-section">
    <div class="container">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="d-inline-block border rounded-pill py-1 px-4">Services</p>
            <h1>Health Care Solutions</h1>
        </div>
        <div class="row g-4">
            @forelse($specialties as $index => $specialty)
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="{{ 0.1 + ($index * 0.2) }}s">
                    <div class="service-item bg-light rounded h-100 p-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle mb-4" style="width: 65px; height: 65px;">
                            @php
                                // Array icon untuk setiap specialty berdasarkan nama
                                $icons = [
                                    'Cardiology' => 'fa fa-heartbeat',
                                    'Pulmonary' => 'fa fa-x-ray',
                                    'Neurology' => 'fa fa-brain',
                                    'Orthopedics' => 'fa fa-wheelchair',
                                    'Dental Surgery' => 'fa fa-tooth',
                                    'Laboratory' => 'fa fa-vials',
                                    'Pediatrics' => 'fa fa-baby',
                                    'Dermatology' => 'fa fa-user-md',
                                    'Ophthalmology' => 'fa fa-eye',
                                    'Psychiatry' => 'fa fa-head-side-virus'
                                ];
                                
                                // Gunakan icon default jika tidak ada yang cocok
                                $iconClass = $icons[$specialty->name] ?? 'fa fa-stethoscope';
                            @endphp
                            <i class="{{ $iconClass }} text-primary fs-4"></i>
                        </div>
                        <h4 class="mb-3">{{ $specialty->name }}</h4>
                        <p class="mb-4">{{ $specialty->description ?? 'Layanan kesehatan berkualitas dengan teknologi terdepan dan tenaga medis berpengalaman.' }}</p>
                        <a class="btn" href="#doctors-section"><i class="fa fa-plus text-primary me-3"></i>Read More</a>
                    </div>
                </div>
            @empty
                <!-- Tampilkan service default jika tidak ada data specialty -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item bg-light rounded h-100 p-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle mb-4" style="width: 65px; height: 65px;">
                            <i class="fa fa-heartbeat text-primary fs-4"></i>
                        </div>
                        <h4 class="mb-3">General Medicine</h4>
                        <p class="mb-4">Layanan kesehatan umum dengan tenaga medis berpengalaman dan fasilitas lengkap.</p>
                        <a class="btn" href="#doctors-section"><i class="fa fa-plus text-primary me-3"></i>Read More</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
<!-- Service End -->
<div class="container-fluid bg-primary overflow-hidden my-5 px-lg-0" id="feature-section">
    <div class="container feature px-lg-0">
        <div class="row g-0 mx-lg-0">
            <div class="col-lg-6 feature-text py-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="p-lg-5 ps-lg-0">
                    <p class="d-inline-block border rounded-pill text-light py-1 px-4">Features</p>
                    <h1 class="text-white mb-4">Why Choose Us</h1>
                    <p class="text-white mb-4 pb-2">When you or a loved one needs healthcare, you want the best. At Hospital of Airlangga, we understand your concerns. That’s why we dedicate ourselves to providing not only cutting-edge medical care, but also a reassuring, personal touch.</p>
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-light" style="width: 55px; height: 55px;">
                                    <i class="fa fa-user-md text-primary"></i>
                                </div>
                                <div class="ms-4">
                                    <p class="text-white mb-2">Experience</p>
                                    <h5 class="text-white mb-0">Doctors</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-light" style="width: 55px; height: 55px;">
                                    <i class="fa fa-check text-primary"></i>
                                </div>
                                <div class="ms-4">
                                    <p class="text-white mb-2">Quality</p>
                                    <h5 class="text-white mb-0">Services</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-light" style="width: 55px; height: 55px;">
                                    <i class="fa fa-comment-medical text-primary"></i>
                                </div>
                                <div class="ms-4">
                                    <p class="text-white mb-2">Positive</p>
                                    <h5 class="text-white mb-0">Consultation</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-light" style="width: 55px; height: 55px;">
                                    <i class="fa fa-headphones text-primary"></i>
                                </div>
                                <div class="ms-4">
                                    <p class="text-white mb-2">24 Hours</p>
                                    <h5 class="text-white mb-0">Support</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 pe-lg-0 wow fadeIn" data-wow-delay="0.5s" style="min-height: 400px;">
                <div class="position-relative h-100">
                    <img class="position-absolute img-fluid w-100 h-100" src="img/feature.jpg" style="object-fit: cover;" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Team Start -->
<div class="container-xxl py-5" id="doctors-section">
    <div class="container">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="d-inline-block border rounded-pill py-1 px-4">Doctors</p>
            <h1>Our Experience Doctors</h1>
        </div>
        <div class="row g-4">
            @forelse($topDoctors as $index => $doctor)
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="{{ 0.1 + ($index * 0.2) }}s">
                    <div class="team-item position-relative rounded overflow-hidden">
                        <div class="overflow-hidden">
                          

                            <!-- Profile Picture -->
                            <img class="img-fluid" src="{{ $doctor->profile_picture ? asset('storage/' . $doctor->profile_picture) : asset('img/default-doctor.jpg') }}" alt="Foto {{ $doctor->name ?? ($doctor->user ? $doctor->user->name : 'Dokter') }}" style="width:100%; height: 280px; object-fit: cover;">

                        </div>
                        <div class="team-text bg-light text-center p-4">
                            <h5>{{ $doctor->user->name ?? 'Dr. Unknown' }}</h5>
                            <p class="text-primary">{{ $doctor->specialty->name ?? 'General Practice' }}</p>
                            
                            <!-- Rating dan Review -->
                            <div class="mb-2">
                                <small class="text-muted">
                                    @if($doctor->feedback_avg_rating > 0)
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($doctor->feedback_avg_rating))
                                                <i class="fa fa-star text-warning"></i>
                                            @else
                                                <i class="fa fa-star text-muted"></i>
                                            @endif
                                        @endfor
                                        {{ number_format($doctor->feedback_avg_rating, 1) }} 
                                        ({{ $doctor->feedback_count }} {{ $doctor->feedback_count == 1 ? 'review' : 'reviews' }})
                                    @else
                                        <span class="text-muted">No reviews yet</span>
                                    @endif
                                </small>
                            </div>

                            <!-- Consultation Fee -->
                            @if($doctor->consultation_fee)
                                <div class="mb-2">
                                    <small class="text-success">
                                        <i class="fa fa-money-bill"></i>
                                        Consultation Fee: Rp {{ number_format($doctor->consultation_fee, 0, ',', '.') }}
                                    </small>
                                </div>
                            @endif

                            <!-- Bio singkat jika ada -->
                            @if($doctor->bio)
                                <div class="mb-2">
                                    <small class="text-muted">
                                        {{ Str::limit($doctor->bio, 80) }}
                                    </small>
                                </div>
                            @endif

                            <div class="team-social text-center">
                                <a class="btn btn-square" href="#" onclick="showDoctorDetail({{ $doctor->id }})" title="View Details">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a class="btn btn-square" href="#" onclick="bookAppointment({{ $doctor->id }})" title="Book Appointment">
                                    <i class="fa fa-calendar-plus"></i>
                                </a>
                                @if($doctor->phone_number)
                                    <a class="btn btn-square" href="tel:{{ $doctor->phone_number }}" title="Call Doctor">
                                        <i class="fa fa-phone"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center">
                        <div class="bg-light rounded p-5">
                            <i class="fa fa-user-md text-primary fs-1 mb-3"></i>
                            <h4 class="text-muted">No Doctors Available</h4>
                            <p class="text-muted">We're currently updating our doctor listings. Please check back soon.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        
        <!-- Tombol untuk melihat semua dokter -->
        @if($topDoctors->count() >= 4)
            <div class="text-center mt-5">
                <a href="{{ route('doctors.index') }}" class="btn btn-primary rounded-pill py-3 px-5">
                    View All Doctors
                </a>
            </div>
        @endif
    </div>
</div>
<!-- Team End -->
<div class="container-xxl py-5" id="contact-section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <p class="d-inline-block border rounded-pill py-1 px-4">Appointment</p>
                <h1 class="mb-4">Make An Appointment To Visit Our Doctor</h1>
                <p class="mb-4">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit. Aliqu diam amet diam et eos. Clita erat ipsum et lorem et sit, sed stet lorem sit clita duo justo magna dolore erat amet</p>
                <div class="bg-light rounded d-flex align-items-center p-5 mb-4">
                    <div class="d-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-white" style="width: 55px; height: 55px;">
                        <i class="fa fa-phone-alt text-primary"></i>
                    </div>
                    <div class="ms-4">
                        <p class="mb-2">Call Us Now</p>
                        <h5 class="mb-0">+012 345 6789</h5>
                    </div>
                </div>
                <div class="bg-light rounded d-flex align-items-center p-5">
                    <div class="d-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-white" style="width: 55px; height: 55px;">
                        <i class="fa fa-envelope-open text-primary"></i>
                    </div>
                    <div class="ms-4">
                        <p class="mb-2">Mail Us Now</p>
                        <h5 class="mb-0">info@example.com</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-light rounded p-5">
                    <p class="d-inline-block border rounded-pill py-1 px-4">Contact Us</p>
                    <h1 class="mb-4">Get in Touch</h1>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" placeholder="Your Name">
                                    <label for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" placeholder="Your Email">
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" placeholder="Subject">
                                    <label for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Leave a message here" id="message" style="height: 100px"></textarea>
                                    <label for="message">Message</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3" type="submit">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonial Start -->
<div class="container-xxl py-5" id="testimonial-section">
    <div class="container">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="d-inline-block border rounded-pill py-1 px-4">Testimonial</p>
            <h1>What Say Our Patients!</h1>
        </div>

        @if($testimonials->count())
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.1s">
                @foreach ($testimonials as $testimonial)
                    <div class="testimonial-item text-center">
                        <div class="bg-light rounded-circle p-2 mx-auto mb-4 d-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px; overflow: hidden;">
                            <img src="{{ $testimonial->patient && $testimonial->patient->profile_picture 
                                        ? asset('storage/' . $testimonial->patient->profile_picture) 
                                        : asset('images/default-profile.png') }}" 
                                 alt="Profile Picture" 
                                 class="img-fluid rounded-circle" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>

                        <div class="testimonial-text rounded text-center p-4">
                            <p>"{{ $testimonial->comment }}"</p>

                            <!-- Rating Stars -->
                            <div class="mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $testimonial->rating)
                                        <i class="fa fa-star text-warning"></i>
                                    @else
                                        <i class="fa fa-star text-muted"></i>
                                    @endif
                                @endfor
                                <span class="ms-2 text-muted">({{ $testimonial->rating }}/5)</span>
                            </div>

                            <h5 class="mb-1">
                                {{ $testimonial->patient && $testimonial->patient->user ? $testimonial->patient->user->name : 'Anonymous Patient' }}
                            </h5>
                            <span class="fst-italic text-primary">
                                Pasien dari Dr. {{ $testimonial->doctor && $testimonial->doctor->user ? $testimonial->doctor->user->name : 'Doctor' }}
                            </span>
                            <br>
                            <small class="text-muted">{{ $testimonial->created_at->format('d M Y') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Fallback jika belum ada testimoni -->
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.1s">
                <div class="testimonial-item text-center">
                    <div class="bg-light rounded-circle p-2 mx-auto mb-4 d-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="fa fa-user text-primary fs-2"></i>
                    </div>
                    <div class="testimonial-text rounded text-center p-4">
                        <p>Pelayanan yang sangat memuaskan. Dokter sangat profesional dan ramah dalam menangani keluhan saya.</p>
                        <div class="mb-3">
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <span class="ms-2 text-muted">(5/5)</span>
                        </div>
                        <h5 class="mb-1">Sample Patient</h5>
                        <span class="fst-italic text-primary">Pasien Umum</span>
                    </div>
                </div>

                <div class="testimonial-item text-center">
                    <div class="bg-light rounded-circle p-2 mx-auto mb-4 d-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="fa fa-user text-primary fs-2"></i>
                    </div>
                    <div class="testimonial-text rounded text-center p-4">
                        <p>Fasilitas rumah sakit yang lengkap dan modern. Staff medis yang kompeten membuat saya merasa aman.</p>
                        <div class="mb-3">
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-muted"></i>
                            <span class="ms-2 text-muted">(4/5)</span>
                        </div>
                        <h5 class="mb-1">Sample Patient 2</h5>
                        <span class="fst-italic text-primary">Pasien Umum</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- Testimonial End -->
