angular.module('stgv2')
		.factory("LehrveranstaltungService", function ($http, $q) {
			var getLVTemplate = function ()
			{
				this.studiengang_kz = null;
				this.bezeichnung = null;
				this.kurzbz = null;
				this.lehrform_kurzbz = null;
				this.semester = 0;
				this.ects = null;
				this.semesterstunden = null;
				this.anmerkung = null;
				this.lehre = true;
				this.lehreverzeichnis = null;
				this.aktiv = true;
				this.insertvon = null;
				this.planfaktor = null;
				this.planlektoren = null;
				this.planpersonalkosten = null;
				this.plankostenprolektor = null;
				this.sort = null;
				this.zeugnis = false;
				this.projektarbeit = false;
				this.sprache = null;
				this.koordinator = null;
				this.bezeichnung_english = null;
				this.orgform_kurzbz = null;
				this.lehrmodus_kurzbz = null;
				this.incoming = null;
				this.lehrtyp_kurzbz = null;
				this.oe_kurzbz = null;
				this.raumtyp_kurzbz = null;
				this.anzahlsemester = null;
				this.semesterwochen = null;
				this.lvnr = null;
				this.semester_alternativ = null;
				this.farbe = null;
				this.sws = null;
				this.lvs = null;
				this.alvs = null;
				this.lvps = null;
				this.las = null;
				this.benotung = false;
				this.lvinfo = false;
				this.lehrauftrag = true;
				this.evaluierung = true;
				this.anmerkung = null;
			};

			return {
				getLVTemplate: getLVTemplate
			};
		});
